import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/auth_session.dart';
import '../models/property.dart';
import '../services/landfocus_api.dart';
import '../services/session_store.dart';

final landFocusApiProvider = Provider<LandFocusApi>(
  (ref) => throw StateError('LandFocusApi has not been configured.'),
);

final sessionStoreProvider = Provider<SessionStore>(
  (ref) => throw StateError('SessionStore has not been configured.'),
);

typedef PropertyListQuery = ({String? type, int limit});

final propertiesProvider = FutureProvider.family<List<Property>, PropertyListQuery>(
  (ref, query) => ref.read(landFocusApiProvider).properties(
    type: query.type,
    limit: query.limit,
  ),
);

final servicesProvider = FutureProvider<List<Map<String, dynamic>>>(
  (ref) => ref.read(landFocusApiProvider).services(),
);

final authControllerProvider =
    AsyncNotifierProvider<AuthController, AuthSession?>(AuthController.new);

class AuthController extends AsyncNotifier<AuthSession?> {
  StreamSubscription<void>? _sessionSubscription;

  @override
  Future<AuthSession?> build() async {
    final store = ref.read(sessionStoreProvider);
    _sessionSubscription = store.changes.listen((_) async {
      state = AsyncData(await store.readSession());
    });
    ref.onDispose(() => _sessionSubscription?.cancel());
    return store.readSession();
  }

  Future<void> signIn({required String email, required String password}) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final session = await ref.read(landFocusApiProvider).login(
        email: email,
        password: password,
      );
      await ref.read(sessionStoreProvider).saveSession(session);
      return session;
    });
  }

  Future<void> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final session = await ref.read(landFocusApiProvider).register(
        name: name,
        email: email,
        phone: phone,
        password: password,
      );
      await ref.read(sessionStoreProvider).saveSession(session);
      return session;
    });
  }

  Future<void> signOut() async {
    try {
      await ref.read(landFocusApiProvider).logout();
    } on Object {
      // Local logout must still complete when the API is unavailable.
    }
    await ref.read(sessionStoreProvider).clearSession();
    state = const AsyncData(null);
  }

  Future<void> updateProfile({
    required String name,
    required String phone,
  }) async {
    final current = state.asData?.value;
    if (current == null) throw StateError('Sign in before editing your profile.');

    final profile = await ref.read(landFocusApiProvider).updateProfile(
      name: name,
      phone: phone,
    );
    final updated = AuthSession(
      token: current.token,
      userId: (profile['id'] ?? current.userId).toString(),
      name: profile['name'] as String? ?? name.trim(),
      email: profile['email'] as String? ?? current.email,
      phone: profile['phone'] as String? ?? phone.trim(),
      role: profile['role'] as String? ?? current.role,
      avatarUrl: profile['avatar_url'] as String? ?? current.avatarUrl,
    );
    await ref.read(sessionStoreProvider).saveSession(updated);
    state = AsyncData(updated);
  }
}

final localeProvider = NotifierProvider<LocaleController, Locale>(
  LocaleController.new,
);

class LocaleController extends Notifier<Locale> {
  @override
  Locale build() => const Locale('sw');

  void toggle() => state = Locale(state.languageCode == 'sw' ? 'en' : 'sw');

  void setLocale(String languageCode) => state = Locale(languageCode);
}

final darkModeProvider = NotifierProvider<DarkModeController, bool>(
  DarkModeController.new,
);

class DarkModeController extends Notifier<bool> {
  @override
  bool build() => false;

  void setEnabled(bool value) => state = value;
}