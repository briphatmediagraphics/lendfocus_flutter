import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/property.dart';

class LandFocusApi {
  LandFocusApi({required String baseUrl, http.Client? client})
    : _baseUrl = baseUrl.replaceFirst(RegExp(r'/+$'), ''),
      _client = client ?? http.Client();

  final String _baseUrl;
  final http.Client _client;

  Future<List<Property>> properties({String? type, int limit = 24}) async {
    final uri = _uri('public/properties', {'type': ?type, 'limit': '$limit'});
    final body = await _send(() => _client.get(uri));
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
    final body = await _send(() => _client.get(_uri('public/properties/$id')));
    final data = body['data'];
    if (data is! Map<String, dynamic>) {
      throw const LandFocusApiException(
        'The server returned invalid property data.',
      );
    }
    return Property.fromJson(data);
  }

  Future<List<Map<String, dynamic>>> services() async {
    final body = await _send(() => _client.get(_uri('public/services')));
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
    await _send(
      () => _client.post(
        _uri('public/inquiries'),
        headers: const {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'name': name.trim(),
          'phone': phone.trim(),
          'message': message.trim(),
          'email': ?_optionalText(email),
          'property_id': ?propertyId,
        }),
      ),
    );
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
    await _send(
      () => _client.post(
        _uri('public/appointments'),
        headers: const {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'client_name': name.trim(),
          'client_phone': phone.trim(),
          'appointment_type': appointmentType,
          'scheduled_at': scheduledAt.toIso8601String(),
          'client_email': ?_optionalText(email),
          'property_id': ?propertyId,
          'notes': ?_optionalText(notes),
        }),
      ),
    );
  }

  Uri _uri(String endpoint, [Map<String, String>? query]) {
    return Uri.parse('$_baseUrl/$endpoint').replace(queryParameters: query);
  }

  String? _optionalText(String? value) {
    final trimmed = value?.trim();
    return trimmed == null || trimmed.isEmpty ? null : trimmed;
  }

  Future<Map<String, dynamic>> _send(
    Future<http.Response> Function() send,
  ) async {
    late final http.Response response;
    try {
      response = await send().timeout(const Duration(seconds: 20));
    } on Exception catch (error) {
      throw LandFocusApiException(
        'Could not reach the LandFocus server. Check that XAMPP/Laravel is running and the API URL is correct.',
        cause: error,
      );
    }

    Map<String, dynamic> body;
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is! Map<String, dynamic>) {
        throw const FormatException('Expected a JSON object.');
      }
      body = decoded;
    } on FormatException catch (error) {
      throw LandFocusApiException(
        'The server returned an invalid response (${response.statusCode}).',
        cause: error,
      );
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      final message = body['message'];
      throw LandFocusApiException(
        message is String && message.isNotEmpty
            ? message
            : 'The request failed (${response.statusCode}).',
        statusCode: response.statusCode,
      );
    }
    return body;
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
