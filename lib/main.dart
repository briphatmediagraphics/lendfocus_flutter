import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import 'app.dart';
import 'services/api_client.dart';
import 'services/landfocus_api.dart';
import 'services/session_store.dart';

const _configuredApiUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: String.fromEnvironment('LANDFOCUS_API_URL'),
);

String get _defaultApiUrl {
  if (kIsWeb) return 'http://localhost:8000/api';
  if (defaultTargetPlatform == TargetPlatform.android) {
    return 'http://10.0.2.2:8000/api';
  }
  return 'http://localhost:8000/api';
}

void main() {
  final sessionStore = SecureSessionStore();
  final dio = createApiClient(
    baseUrl: _configuredApiUrl.isEmpty ? _defaultApiUrl : _configuredApiUrl,
    sessionStore: sessionStore,
  );
  final api = LandFocusApi(dio: dio);
  runApp(LandFocusApp(api: api, sessionStore: sessionStore));
}
