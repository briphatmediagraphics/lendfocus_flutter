import 'dart:async';
import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/auth_session.dart';

abstract interface class SessionStore {
  Stream<void> get changes;
  Future<AuthSession?> readSession();
  Future<void> saveSession(AuthSession session);
  Future<void> clearSession();
  Future<bool> hasSeenOnboarding();
  Future<void> setOnboardingSeen();
}

class SecureSessionStore implements SessionStore {
  SecureSessionStore({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  static const _sessionKey = 'landfocus.session';
  static const _onboardingKey = 'landfocus.onboarding_seen';

  final FlutterSecureStorage _storage;
  final StreamController<void> _changes = StreamController<void>.broadcast();

  @override
  Stream<void> get changes => _changes.stream;

  @override
  Future<AuthSession?> readSession() async {
    final value = await _storage.read(key: _sessionKey);
    if (value == null) return null;
    try {
      return AuthSession.fromStorageJson(
        jsonDecode(value) as Map<String, dynamic>,
      );
    } on Object {
      await clearSession();
      return null;
    }
  }

  @override
  Future<void> saveSession(AuthSession session) async {
    await _storage.write(
      key: _sessionKey,
      value: jsonEncode(session.toStorageJson()),
    );
    _changes.add(null);
  }

  @override
  Future<void> clearSession() async {
    await _storage.delete(key: _sessionKey);
    _changes.add(null);
  }

  @override
  Future<bool> hasSeenOnboarding() async =>
      await _storage.read(key: _onboardingKey) == 'true';

  @override
  Future<void> setOnboardingSeen() =>
      _storage.write(key: _onboardingKey, value: 'true');
}

class MemorySessionStore implements SessionStore {
  final StreamController<void> _changes = StreamController<void>.broadcast();
  AuthSession? _session;
  bool _onboardingSeen = false;

  @override
  Stream<void> get changes => _changes.stream;

  @override
  Future<AuthSession?> readSession() async => _session;

  @override
  Future<void> saveSession(AuthSession session) async {
    _session = session;
    _changes.add(null);
  }

  @override
  Future<void> clearSession() async {
    _session = null;
    _changes.add(null);
  }

  @override
  Future<bool> hasSeenOnboarding() async => _onboardingSeen;

  @override
  Future<void> setOnboardingSeen() async {
    _onboardingSeen = true;
  }
}