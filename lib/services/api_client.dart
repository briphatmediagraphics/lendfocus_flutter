import 'dart:async';

import 'package:dio/dio.dart';

import 'session_store.dart';

Dio createApiClient({
  required String baseUrl,
  required SessionStore sessionStore,
}) {
  final dio = Dio(
    BaseOptions(
      baseUrl: '${baseUrl.replaceFirst(RegExp(r'/+$'), '')}/',
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      sendTimeout: const Duration(seconds: 20),
      headers: const {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final session = await sessionStore.readSession();
        if (session != null) {
          options.headers['Authorization'] = 'Bearer ${session.token}';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        if (error.response?.statusCode == 401) {
          unawaited(sessionStore.clearSession());
        }
        handler.next(error);
      },
    ),
  );

  return dio;
}