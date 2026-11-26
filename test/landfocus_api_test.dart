import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:landfocus_app/models/auth_session.dart';
import 'package:landfocus_app/models/property.dart';
import 'package:landfocus_app/services/api_client.dart';
import 'package:landfocus_app/services/landfocus_api.dart';
import 'package:landfocus_app/services/session_store.dart';

void main() {
  group('Property.fromJson', () {
    test('parses nullable details and numeric strings from Laravel JSON', () {
      final property = Property.fromJson({
        'id': 'property-1',
        'title': 'Coastal plot',
        'location': 'Dar es Salaam',
        'property_type': 'land',
        'status': 'available',
        'price': '12500000',
        'currency': 'TZS',
        'images': ['https://example.test/plot.jpg', null],
        'area_sqm': '450',
        'featured': true,
      });

      expect(property.price, 12500000);
      expect(property.areaSqm, 450);
      expect(property.bedrooms, isNull);
      expect(property.images, ['https://example.test/plot.jpg']);
      expect(property.featured, isTrue);
    });
  });

  group('LandFocusApi', () {
    test('logs in through the versioned API and parses the session', () async {
      late RequestOptions request;
      final api = LandFocusApi(
        dio: _dio('http://localhost:8000/api', (incoming) {
          request = incoming;
          return _response({
            'success': true,
            'data': {
              'access_token': 'session-token',
              'user': {
                'id': 42,
                'name': 'Asha Mushi',
                'email': 'asha@example.test',
                'phone': '0712345678',
                'role': 'customer',
              },
            },
          }, 200);
        }),
      );

      final session = await api.login(
        email: ' asha@example.test ',
        password: 'a-strong-password',
      );

      expect(request.uri.path, '/api/v1/auth/login');
      expect(request.data, {
        'email': 'asha@example.test',
        'password': 'a-strong-password',
      });
      expect(session.token, 'session-token');
      expect(session.userId, '42');
      expect(session.name, 'Asha Mushi');
      expect(session.role, 'customer');
    });

    test('loads properties with type and limit query parameters', () async {
      late Uri requestedUri;
      final api = LandFocusApi(
        dio: _dio('http://localhost:8000/api/', (request) {
          requestedUri = request.uri;
          return _response({'data': [_propertyJson]}, 200);
        }),
      );

      final properties = await api.properties(type: 'land', limit: 8);

      expect(requestedUri.path, '/api/public/properties');
      expect(requestedUri.queryParameters, {'type': 'land', 'limit': '8'});
      expect(properties.single.id, 'property-1');
    });

    test('sends inquiries as JSON and trims user input', () async {
      late RequestOptions request;
      final api = LandFocusApi(
        dio: _dio('http://localhost:8000/api', (incoming) {
          request = incoming;
          return _response({}, 201);
        }),
      );

      await api.submitInquiry(
        name: '  Asha  ',
        phone: ' 0712345678 ',
        message: '  I would like details about this land. ',
        propertyId: 'property-1',
      );

      expect(request.method, 'POST');
      expect(request.headers['content-type'], 'application/json');
      expect(request.data, {
        'name': 'Asha',
        'phone': '0712345678',
        'message': 'I would like details about this land.',
        'property_id': 'property-1',
      });
    });

    test(
      'reports Laravel error responses instead of treating them as success',
      () async {
        final api = LandFocusApi(
          dio: _dio(
            'http://localhost:8000/api',
            (_) => _response(
              {'message': 'The phone field is required.'},
              422,
            ),
          ),
        );

        expect(
          () => api.properties(),
          throwsA(
            isA<LandFocusApiException>()
                .having((error) => error.statusCode, 'status code', 422)
                .having(
                  (error) => error.message,
                  'message',
                  'The phone field is required.',
                ),
          ),
        );
      },
    );
  });

  test('Dio attaches the saved bearer token and clears it after a 401', () async {
    final sessionStore = MemorySessionStore();
    await sessionStore.saveSession(
      const AuthSession(
        token: 'expired-token',
        userId: '7',
        name: 'Test User',
        email: 'test@example.test',
      ),
    );
    late RequestOptions request;
    final dio = createApiClient(
      baseUrl: 'http://localhost:8000/api',
      sessionStore: sessionStore,
    );
    dio.httpClientAdapter = _MockAdapter((incoming) {
      request = incoming;
      return _response({'message': 'Unauthenticated.'}, 401);
    });

    await expectLater(dio.get('v1/profile'), throwsA(isA<DioException>()));
    await Future<void>.delayed(Duration.zero);

    expect(request.headers['Authorization'], 'Bearer expired-token');
    expect(await sessionStore.readSession(), isNull);
  });
}

const _propertyJson = {
  'id': 'property-1',
  'title': 'Coastal plot',
  'location': 'Dar es Salaam',
  'property_type': 'land',
  'status': 'available',
  'price': 12500000,
  'currency': 'TZS',
  'images': <String>[],
};

Dio _dio(String baseUrl, _AdapterHandler handler) {
  final normalizedBaseUrl = baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';
  final dio = Dio(BaseOptions(baseUrl: normalizedBaseUrl));
  dio.httpClientAdapter = _MockAdapter(handler);
  return dio;
}

ResponseBody _response(Map<String, dynamic> body, int statusCode) =>
    ResponseBody.fromString(
      jsonEncode(body),
      statusCode,
      headers: const {'content-type': ['application/json']},
    );

typedef _AdapterHandler = ResponseBody Function(RequestOptions request);

class _MockAdapter implements HttpClientAdapter {
  _MockAdapter(this.handler);

  final _AdapterHandler handler;

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async => handler(options);

  @override
  void close({bool force = false}) {}
}
