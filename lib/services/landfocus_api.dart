import 'package:dio/dio.dart';

import '../models/auth_session.dart';
import '../models/property.dart';

class LandFocusApi {
  LandFocusApi({required this._dio});

  final Dio _dio;

  Future<List<Property>> properties({String? type, int limit = 24}) async {
    final response = await _get('public/properties', {
      'type': ?type,
      'limit': '$limit',
    });
    final body = _asMap(response.data);
    final data = body['data'];
    if (data is! List) {
      throw const LandFocusApiException(
        'The server returned invalid property data.',
      );
    }
    return data
        .whereType<Map<String, dynamic>>()
        .map(Property.fromJson)
        .toList(growable: false);
  }

  Future<Property> property(String id) async {
    final response = await _get('public/properties/$id');
    final body = _asMap(response.data);
    final data = body['data'];
    if (data is! Map<String, dynamic>) {
      throw const LandFocusApiException(
        'The server returned invalid property data.',
      );
    }
    return Property.fromJson(data);
  }

  Future<List<Map<String, dynamic>>> services() async {
    final response = await _get('public/services');
    final body = _asMap(response.data);
    final data = body['data'];
    if (data is! List) {
      throw const LandFocusApiException(
        'The server returned invalid service data.',
      );
    }
    return data.whereType<Map<String, dynamic>>().toList(growable: false);
  }

  Future<void> submitInquiry({
    required String name,
    required String phone,
    required String message,
    String? email,
    String? propertyId,
  }) async {
    await _post('public/inquiries', {
      'name': name.trim(),
      'phone': phone.trim(),
      'message': message.trim(),
      'email': ?_optionalText(email),
      'property_id': ?propertyId,
    });
  }

  Future<void> submitAppointment({
    required String name,
    required String phone,
    required String appointmentType,
    required DateTime scheduledAt,
    String? email,
    String? propertyId,
    String? notes,
  }) async {
    await _post('public/appointments', {
      'client_name': name.trim(),
      'client_phone': phone.trim(),
      'appointment_type': appointmentType,
      'scheduled_at': scheduledAt.toIso8601String(),
      'client_email': ?_optionalText(email),
      'property_id': ?propertyId,
      'notes': ?_optionalText(notes),
    });
  }

  Future<AuthSession> login({
    required String email,
    required String password,
  }) async {
    final body = await _post('v1/auth/login', {
      'email': email.trim(),
      'password': password,
    });
    return AuthSession.fromJson(_dataMap(body));
  }

  Future<AuthSession> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    final body = await _post('v1/auth/register', {
      'name': name.trim(),
      'email': email.trim(),
      'phone': phone.trim(),
      'password': password,
      'password_confirmation': password,
    });
    return AuthSession.fromJson(_dataMap(body));
  }

  Future<void> requestPasswordReset(String email) async {
    await _post('v1/auth/forgot-password', {'email': email.trim()});
  }

  Future<void> resetPassword({
    required String email,
    required String token,
    required String password,
  }) async {
    await _post('v1/auth/reset-password', {
      'email': email.trim(),
      'token': token,
      'password': password,
      'password_confirmation': password,
    });
  }

  Future<void> logout() async {
    await _post('v1/auth/logout', const {});
  }

  Future<Map<String, dynamic>> profile() async {
    final response = await _get('v1/profile');
    return _dataMap(_asMap(response.data));
  }

  Future<Map<String, dynamic>> updateProfile({
    required String name,
    required String phone,
  }) async {
    final response = await _request(
      () => _dio.patch(
        'v1/profile',
        data: {'name': name.trim(), 'phone': phone.trim()},
      ),
    );
    return _dataMap(_asMap(response.data));
  }

  Future<Response<dynamic>> _get(
    String path, [
    Map<String, String>? query,
  ]) => _request(() => _dio.get(path, queryParameters: query));

  Future<Map<String, dynamic>> _post(
    String path,
    Map<String, dynamic> data,
  ) async {
    final response = await _request(() => _dio.post(path, data: data));
    return _asMap(response.data);
  }

  Future<Response<dynamic>> _request(
    Future<Response<dynamic>> Function() request,
  ) async {
    try {
      return await request();
    } on DioException catch (error) {
      final response = error.response;
      final body = response?.data;
      final bodyMap = body is Map<String, dynamic> ? body : const {};
      final errors = bodyMap['errors'];
      final validationMessage = errors is Map
          ? errors.values
                .whereType<List>()
                .expand((messages) => messages)
                .whereType<String>()
                .firstOrNull
          : null;
      final message = validationMessage ?? bodyMap['message'];
      throw LandFocusApiException(
        message is String && message.isNotEmpty
            ? message
            : response == null
            ? 'Could not reach the LandFocus server. Check your API URL and network connection.'
            : 'The request failed (${response.statusCode}).',
        statusCode: response?.statusCode,
        cause: error,
      );
    }
  }

  Map<String, dynamic> _asMap(dynamic data) {
    if (data is Map<String, dynamic>) return data;
    throw const LandFocusApiException('The server returned invalid JSON data.');
  }

  Map<String, dynamic> _dataMap(Map<String, dynamic> body) {
    final data = body['data'];
    if (data is Map<String, dynamic>) return data;
    return body;
  }

  String? _optionalText(String? value) {
    final trimmed = value?.trim();
    return trimmed == null || trimmed.isEmpty ? null : trimmed;
  }

}

class LandFocusApiException implements Exception {
  const LandFocusApiException(this.message, {this.statusCode, this.cause});

  final String message;
  final int? statusCode;
  final Object? cause;

  @override
  String toString() => message;
}
