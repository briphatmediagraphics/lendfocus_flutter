import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:landfocus_app/app.dart';
import 'package:landfocus_app/services/landfocus_api.dart';

void main() {
  testWidgets('shows the LandFocus home and supports English', (tester) async {
    final api = LandFocusApi(
      baseUrl: 'http://localhost:8000/api',
      client: MockClient((request) async {
        if (request.url.path.endsWith('/services')) {
          return http.Response(jsonEncode({'data': []}), 200);
        }
        return http.Response(jsonEncode({'data': []}), 200);
      }),
    );

    await tester.pumpWidget(LandFocusApp(api: api));
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
}
