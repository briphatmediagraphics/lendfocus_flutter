import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:landfocus_app/models/property.dart';
import 'package:landfocus_app/services/landfocus_api.dart';

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
    test('loads properties with type and limit query parameters', () async {
      late Uri requestedUri;
      final api = LandFocusApi(
        baseUrl: 'http://localhost:8000/api/',
        client: MockClient((request) async {
          requestedUri = request.url;
          return http.Response(
            jsonEncode({
              'data': [_propertyJson],
            }),
            200,
          );
        }),
      );

      final properties = await api.properties(type: 'land', limit: 8);

      expect(requestedUri.path, '/api/public/properties');
      expect(requestedUri.queryParameters, {'type': 'land', 'limit': '8'});
      expect(properties.single.id, 'property-1');
    });

    test('sends inquiries as JSON and trims user input', () async {
      late http.Request request;
      final api = LandFocusApi(
        baseUrl: 'http://localhost:8000/api',
        client: MockClient((incoming) async {
          request = incoming;
          return http.Response('{}', 201);
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
      expect(jsonDecode(request.body), {
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
          baseUrl: 'http://localhost:8000/api',
          client: MockClient(
            (_) async => http.Response(
              jsonEncode({'message': 'The phone field is required.'}),
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
