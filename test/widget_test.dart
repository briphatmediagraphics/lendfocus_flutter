import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:landfocus_app/app.dart';
import 'package:landfocus_app/services/landfocus_api.dart';
import 'package:landfocus_app/services/session_store.dart';

void main() {
  testWidgets('shows the LandFocus home and supports English', (tester) async {
    final sessionStore = MemorySessionStore();
    await sessionStore.setOnboardingSeen();
    final api = LandFocusApi(
      dio: _testDio(),
    );

    await tester.pumpWidget(
      LandFocusApp(api: api, sessionStore: sessionStore),
    );
    await tester.pumpAndSettle();

    expect(find.text('LandFocus'), findsOneWidget);
    expect(
      find.text('Pata Ardhi, Nyumba na Mashamba kwa Urahisi'),
      findsOneWidget,
    );

    await tester.tap(find.text('EN'));
    await tester.pumpAndSettle();

    expect(
      find.text('Find Land, Homes and Farms with Confidence'),
      findsOneWidget,
    );
    expect(find.text('Available properties'), findsOneWidget);
  });

  testWidgets('guest account routes to login and settings stay available', (
    tester,
  ) async {
    final sessionStore = MemorySessionStore();
    await sessionStore.setOnboardingSeen();
    await tester.pumpWidget(
      LandFocusApp(api: LandFocusApi(dio: _testDio()), sessionStore: sessionStore),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.byTooltip('Akaunti yangu'));
    await tester.pumpAndSettle();
    expect(find.text('Karibu tena'), findsOneWidget);

    await tester.tap(find.text('Endelea bila akaunti'));
    await tester.pumpAndSettle();
    await tester.tap(find.byTooltip('Mipangilio'));
    await tester.pumpAndSettle();
    expect(find.text('Mipangilio'), findsOneWidget);

    await tester.tap(find.text('English'));
    await tester.pumpAndSettle();
    expect(find.text('Settings'), findsOneWidget);
  });
}

Dio _testDio() {
  final dio = Dio(BaseOptions(baseUrl: 'http://localhost:8000/api'));
  dio.httpClientAdapter = _WidgetMockAdapter();
  return dio;
}

class _WidgetMockAdapter implements HttpClientAdapter {
  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async => ResponseBody.fromString(
    jsonEncode({'data': []}),
    200,
    headers: const {'content-type': ['application/json']},
  );

  @override
  void close({bool force = false}) {}
}
