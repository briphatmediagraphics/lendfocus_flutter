import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'app.dart';
import 'services/landfocus_api.dart';

const _configuredApiUrl = String.fromEnvironment('LANDFOCUS_API_URL');

String get _defaultApiUrl {
  if (kIsWeb) return 'http://localhost:8000/api';
  if (defaultTargetPlatform == TargetPlatform.android) {
    return 'http://10.0.2.2:8000/api';
  }
  return 'http://localhost:8000/api';
}

void main() {
  final api = LandFocusApi(
    baseUrl: _configuredApiUrl.isEmpty ? _defaultApiUrl : _configuredApiUrl,
    client: http.Client(),
  );
  runApp(LandFocusApp(api: api));
}
