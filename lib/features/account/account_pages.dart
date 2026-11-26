import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/auth_session.dart';
import '../../providers/app_providers.dart';
import '../../services/landfocus_api.dart';

String _accountText(BuildContext context, String swahili, String english) =>
    Localizations.localeOf(context).languageCode == 'sw' ? swahili : english;

class ProfilePage extends ConsumerWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(authControllerProvider).asData?.value;
    if (session == null) {
      return Scaffold(
        appBar: AppBar(title: Text(_accountText(context, 'Akaunti', 'Account'))),
        body: _EmptyAccount(
          onSignIn: () => context.go('/login?redirect=%2Fprofile'),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: Text(_accountText(context, 'Akaunti yangu', 'My account'))),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
        children: [
          _ProfileIdentity(session: session),
          const SizedBox(height: 18),
          _ProfileDetail(
            icon: Icons.mail_outline,
            label: _accountText(context, 'Barua pepe', 'Email'),
            value: session.email,
          ),
          _ProfileDetail(
            icon: Icons.phone_outlined,
            label: _accountText(context, 'Simu', 'Phone'),
            value: session.phone?.isNotEmpty == true ? session.phone! : '—',
          ),
          if (session.role != null)
            _ProfileDetail(
              icon: Icons.badge_outlined,
              label: _accountText(context, 'Aina ya akaunti', 'Account type'),
              value: session.role!,
            ),
          const SizedBox(height: 14),
          OutlinedButton.icon(
            onPressed: () => context.push('/profile/edit'),
            icon: const Icon(Icons.edit_outlined),
            label: Text(_accountText(context, 'Hariri taarifa', 'Edit profile')),
          ),
          const SizedBox(height: 8),
          OutlinedButton.icon(
            onPressed: () => context.push('/settings'),
            icon: const Icon(Icons.settings_outlined),
            label: Text(_accountText(context, 'Mipangilio', 'Settings')),
          ),
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: () async {
              await ref.read(authControllerProvider.notifier).signOut();
              if (context.mounted) context.go('/home');
            },
            icon: const Icon(Icons.logout),
            label: Text(_accountText(context, 'Toka kwenye akaunti', 'Sign out')),
          ),
        ],
      ),
    );
  }
}

class ProfileEditPage extends ConsumerStatefulWidget {
  const ProfileEditPage({super.key});

  @override
  ConsumerState<ProfileEditPage> createState() => _ProfileEditPageState();
}

class _ProfileEditPageState extends ConsumerState<ProfileEditPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _name;
  late final TextEditingController _phone;
  bool _saving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    final session = ref.read(authControllerProvider).asData?.value;
    _name = TextEditingController(text: session?.name ?? '');
    _phone = TextEditingController(text: session?.phone ?? '');
  }

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = null;
    });
    try {
      await ref.read(authControllerProvider.notifier).updateProfile(
        name: _name.text,
        phone: _phone.text,
      );
      if (mounted) context.pop();
    } on LandFocusApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } on Object catch (error) {
      if (mounted) setState(() => _error = error.toString());
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_accountText(context, 'Hariri taarifa', 'Edit profile'))),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 520),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  TextFormField(
                    controller: _name,
                    textCapitalization: TextCapitalization.words,
                    decoration: InputDecoration(
                      labelText: _accountText(context, 'Jina kamili', 'Full name'),
                    ),
                    validator: (value) => value == null || value.trim().length < 2
                        ? _accountText(context, 'Weka jina lako.', 'Enter your name.')
                        : null,
                  ),
                  const SizedBox(height: 14),
                  TextFormField(
                    controller: _phone,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      labelText: _accountText(context, 'Namba ya simu', 'Phone number'),
                    ),
                    validator: (value) => value == null || value.trim().length < 7
                        ? _accountText(context, 'Weka namba sahihi.', 'Enter a valid phone number.')
                        : null,
                  ),
                  if (_error != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 14),
                      child: Text(
                        _error!,
                        style: TextStyle(color: Theme.of(context).colorScheme.error),
                      ),
                    ),
                  const SizedBox(height: 18),
                  FilledButton(
                    onPressed: _saving ? null : _save,
                    child: _saving
                        ? const SizedBox.square(
                            dimension: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Text(_accountText(context, 'Hifadhi mabadiliko', 'Save changes')),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class SettingsPage extends ConsumerWidget {
  const SettingsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final locale = ref.watch(localeProvider);
    final darkMode = ref.watch(darkModeProvider);
    return Scaffold(
      appBar: AppBar(title: Text(_accountText(context, 'Mipangilio', 'Settings'))),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 28),
        children: [
          Text(
            _accountText(context, 'Lugha', 'Language'),
            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 10),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'sw', label: Text('Kiswahili')),
              ButtonSegment(value: 'en', label: Text('English')),
            ],
            selected: {locale.languageCode},
            onSelectionChanged: (value) =>
                ref.read(localeProvider.notifier).setLocale(value.first),
          ),
          const SizedBox(height: 24),
          SwitchListTile.adaptive(
            contentPadding: EdgeInsets.zero,
            title: Text(_accountText(context, 'Mandhari meusi', 'Dark appearance')),
            subtitle: Text(_accountText(context, 'Tumia mandhari yenye mwanga mdogo.', 'Use a darker color scheme.')),
            value: darkMode,
            onChanged: ref.read(darkModeProvider.notifier).setEnabled,
          ),
          const Divider(height: 28),
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const Icon(Icons.privacy_tip_outlined),
            title: Text(_accountText(context, 'Faragha na usalama', 'Privacy and security')),
            subtitle: Text(_accountText(context, 'Tokeni huhifadhiwa kwa usalama kwenye kifaa hiki.', 'Your sign-in token is stored securely on this device.')),
          ),
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const Icon(Icons.info_outline),
            title: Text(_accountText(context, 'LandFocus', 'LandFocus')),
            subtitle: const Text('Version 1.0.0'),
          ),
        ],
      ),
    );
  }
}

class _ProfileIdentity extends StatelessWidget {
  const _ProfileIdentity({required this.session});

  final AuthSession session;

  @override
  Widget build(BuildContext context) => Row(
    children: [
      CircleAvatar(
        radius: 32,
        backgroundColor: Theme.of(context).colorScheme.primaryContainer,
        foregroundImage: session.avatarUrl == null
            ? null
            : NetworkImage(session.avatarUrl!),
        child: session.avatarUrl == null
            ? Text(
                session.name.isEmpty ? '?' : session.name[0].toUpperCase(),
                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 22),
              )
            : null,
      ),
      const SizedBox(width: 14),
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(session.name, style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w800)),
            Text(session.email, style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant)),
          ],
        ),
      ),
    ],
  );
}

class _ProfileDetail extends StatelessWidget {
  const _ProfileDetail({required this.icon, required this.label, required this.value});

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) => ListTile(
    contentPadding: EdgeInsets.zero,
    leading: Icon(icon, color: Theme.of(context).colorScheme.primary),
    title: Text(label, style: Theme.of(context).textTheme.labelMedium),
    subtitle: Text(value, style: Theme.of(context).textTheme.bodyLarge),
  );
}

class _EmptyAccount extends StatelessWidget {
  const _EmptyAccount({required this.onSignIn});

  final VoidCallback onSignIn;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.person_outline, size: 44),
          const SizedBox(height: 12),
          Text(_accountText(context, 'Ingia ili kuona akaunti yako.', 'Sign in to view your account.')),
          const SizedBox(height: 14),
          FilledButton(onPressed: onSignIn, child: Text(_accountText(context, 'Ingia', 'Sign in'))),
        ],
      ),
    ),
  );
}