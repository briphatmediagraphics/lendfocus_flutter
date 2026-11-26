import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/app_providers.dart';
import '../../services/landfocus_api.dart';

String _authText(BuildContext context, String swahili, String english) =>
    Localizations.localeOf(context).languageCode == 'sw' ? swahili : english;

class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage> {
  @override
  void initState() {
    super.initState();
    _continue();
  }

  Future<void> _continue() async {
    try {
      await ref.read(authControllerProvider.future);
    } on Object {
      // An unavailable secure store should not block the public app.
    }
    final seen = await ref.read(sessionStoreProvider).hasSeenOnboarding();
    await Future<void>.delayed(const Duration(milliseconds: 450));
    if (mounted) context.go(seen ? '/home' : '/onboarding');
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Image.asset(
            'assets/images/landfocus-logo.png',
            width: 76,
            height: 76,
          ),
          const SizedBox(height: 14),
          const Text(
            'LandFocus',
            style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 18),
          const SizedBox(
            width: 22,
            height: 22,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
        ],
      ),
    ),
  );
}

class OnboardingPage extends StatefulWidget {
  const OnboardingPage({super.key});

  @override
  State<OnboardingPage> createState() => _OnboardingPageState();
}

class _OnboardingPageState extends State<OnboardingPage> {
  final _controller = PageController();
  int _page = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _finish(BuildContext context, WidgetRef ref) async {
    await ref.read(sessionStoreProvider).setOnboardingSeen();
    if (context.mounted) context.go('/home');
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      (
        'assets/images/sample-land.jpg',
        'Pata ardhi kwa uhakika',
        'Browse verified land and property opportunities across Tanzania.',
      ),
      (
        'assets/images/sample-house.jpg',
        'Chagua kinachokufaa',
        'Compare properties, locations, sizes and prices in one place.',
      ),
      (
        'assets/images/sample-farm.jpg',
        'Panga hatua inayofuata',
        'Ask our team a question or request an appointment when you are ready.',
      ),
    ];

    return Consumer(
      builder: (context, ref, _) => Scaffold(
        body: SafeArea(
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 680),
              child: Column(
                children: [
                  Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => _finish(context, ref),
                      child: Text(_authText(context, 'Ruka', 'Skip')),
                    ),
                  ),
                  Expanded(
                    child: PageView.builder(
                      controller: _controller,
                      itemCount: pages.length,
                      onPageChanged: (value) => setState(() => _page = value),
                      itemBuilder: (context, index) {
                        final page = pages[index];
                        return Padding(
                          padding: const EdgeInsets.fromLTRB(24, 8, 24, 16),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              AspectRatio(
                                aspectRatio: 1.2,
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Image.asset(
                                    page.$1,
                                    fit: BoxFit.cover,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 28),
                              Text(
                                page.$2,
                                textAlign: TextAlign.center,
                                style: Theme.of(context)
                                    .textTheme
                                    .headlineSmall
                                    ?.copyWith(fontWeight: FontWeight.w800),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                page.$3,
                                textAlign: TextAlign.center,
                                style: Theme.of(context).textTheme.bodyLarge
                                    ?.copyWith(color: Colors.black54, height: 1.5),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
                    child: Row(
                      children: [
                        Expanded(
                          child: Row(
                            children: List.generate(
                              pages.length,
                              (index) => AnimatedContainer(
                                duration: const Duration(milliseconds: 180),
                                margin: const EdgeInsets.only(right: 6),
                                width: index == _page ? 22 : 7,
                                height: 7,
                                decoration: BoxDecoration(
                                  color: index == _page
                                      ? Theme.of(context).colorScheme.primary
                                      : Colors.black26,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                            ),
                          ),
                        ),
                        FilledButton.icon(
                          onPressed: () {
                            if (_page == pages.length - 1) {
                              _finish(context, ref);
                            } else {
                              _controller.nextPage(
                                duration: const Duration(milliseconds: 240),
                                curve: Curves.easeOut,
                              );
                            }
                          },
                          icon: Icon(
                            _page == pages.length - 1
                                ? Icons.check_rounded
                                : Icons.arrow_forward_rounded,
                          ),
                          label: Text(
                            _page == pages.length - 1
                                ? _authText(context, 'Anza', 'Get started')
                                : _authText(context, 'Endelea', 'Continue'),
                          ),
                        ),
                      ],
                    ),
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

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final loading = auth.isLoading;
    return _AuthPageFrame(
      title: _authText(context, 'Karibu tena', 'Welcome back'),
      subtitle: _authText(
        context,
        'Ingia kwenye akaunti yako ya LandFocus.',
        'Sign in to your LandFocus account.',
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextFormField(
              controller: _email,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
              decoration: InputDecoration(
                labelText: _authText(context, 'Barua pepe', 'Email'),
                prefixIcon: const Icon(Icons.mail_outline),
              ),
              validator: (value) => value == null || !value.contains('@')
                  ? _authText(context, 'Weka barua pepe sahihi.', 'Enter a valid email.')
                  : null,
            ),
            const SizedBox(height: 14),
            TextFormField(
              controller: _password,
              obscureText: _obscurePassword,
              autofillHints: const [AutofillHints.password],
              decoration: InputDecoration(
                labelText: _authText(context, 'Nenosiri', 'Password'),
                prefixIcon: const Icon(Icons.lock_outline),
                suffixIcon: IconButton(
                  tooltip: _obscurePassword ? 'Show password' : 'Hide password',
                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                  ),
                ),
              ),
              validator: (value) => value == null || value.isEmpty
                  ? _authText(context, 'Weka nenosiri.', 'Enter your password.')
                  : null,
            ),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton(
                onPressed: () => context.push('/forgot-password'),
                child: Text(_authText(context, 'Umesahau nenosiri?', 'Forgot password?')),
              ),
            ),
            if (auth.hasError) _AuthError(message: _errorMessage(auth.error)),
            const SizedBox(height: 6),
            FilledButton(
              onPressed: loading
                  ? null
                  : () {
                      if (_formKey.currentState!.validate()) {
                        ref.read(authControllerProvider.notifier).signIn(
                          email: _email.text,
                          password: _password.text,
                        );
                      }
                    },
              child: loading
                  ? const SizedBox.square(
                      dimension: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text(_authText(context, 'Ingia', 'Sign in')),
            ),
            const SizedBox(height: 14),
            TextButton(
              onPressed: () => context.push('/register'),
              child: Text(_authText(context, 'Tengeneza akaunti', 'Create an account')),
            ),
            TextButton(
              onPressed: () => context.go('/home'),
              child: Text(_authText(context, 'Endelea bila akaunti', 'Continue as guest')),
            ),
          ],
        ),
      ),
    );
  }
}

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _password = TextEditingController();
  final _confirmation = TextEditingController();

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _password.dispose();
    _confirmation.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    return _AuthPageFrame(
      title: _authText(context, 'Fungua akaunti', 'Create your account'),
      subtitle: _authText(
        context,
        'Hifadhi miradi unayopenda na fuatilia maombi yako.',
        'Keep track of inquiries and property appointments.',
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _authInput(context, _name, _authText(context, 'Jina kamili', 'Full name'), Icons.person_outline),
            const SizedBox(height: 12),
            TextFormField(
              controller: _email,
              keyboardType: TextInputType.emailAddress,
              decoration: InputDecoration(
                labelText: _authText(context, 'Barua pepe', 'Email'),
                prefixIcon: const Icon(Icons.mail_outline),
              ),
              validator: (value) => value == null || !value.contains('@')
                  ? _authText(context, 'Weka barua pepe sahihi.', 'Enter a valid email.')
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _phone,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: _authText(context, 'Namba ya simu', 'Phone number'),
                prefixIcon: const Icon(Icons.phone_outlined),
              ),
              validator: (value) => value == null || value.trim().length < 7
                  ? _authText(context, 'Weka namba sahihi.', 'Enter a valid phone number.')
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _password,
              obscureText: true,
              decoration: InputDecoration(
                labelText: _authText(context, 'Nenosiri', 'Password'),
                prefixIcon: const Icon(Icons.lock_outline),
              ),
              validator: (value) => value == null || value.length < 8
                  ? _authText(context, 'Tumia angalau herufi 8.', 'Use at least 8 characters.')
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _confirmation,
              obscureText: true,
              decoration: InputDecoration(
                labelText: _authText(context, 'Rudia nenosiri', 'Confirm password'),
                prefixIcon: const Icon(Icons.lock_outline),
              ),
              validator: (value) => value != _password.text
                  ? _authText(context, 'Nenosiri halifanani.', 'Passwords do not match.')
                  : null,
            ),
            if (auth.hasError) _AuthError(message: _errorMessage(auth.error)),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: auth.isLoading
                  ? null
                  : () {
                      if (_formKey.currentState!.validate()) {
                        ref.read(authControllerProvider.notifier).register(
                          name: _name.text,
                          email: _email.text,
                          phone: _phone.text,
                          password: _password.text,
                        );
                      }
                    },
              child: auth.isLoading
                  ? const SizedBox.square(
                      dimension: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : Text(_authText(context, 'Tengeneza akaunti', 'Create account')),
            ),
            TextButton(
              onPressed: () => context.go('/login'),
              child: Text(_authText(context, 'Tayari una akaunti? Ingia', 'Already registered? Sign in')),
            ),
          ],
        ),
      ),
    );
  }
}

class ForgotPasswordPage extends ConsumerStatefulWidget {
  const ForgotPasswordPage({super.key});

  @override
  ConsumerState<ForgotPasswordPage> createState() => _ForgotPasswordPageState();
}

class _ForgotPasswordPageState extends ConsumerState<ForgotPasswordPage> {
  final _email = TextEditingController();
  bool _sending = false;
  String? _error;
  bool _sent = false;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      _sending = true;
      _error = null;
    });
    try {
      await ref.read(landFocusApiProvider).requestPasswordReset(_email.text);
      if (mounted) setState(() => _sent = true);
    } on LandFocusApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) => _AuthPageFrame(
    title: _authText(context, 'Rejesha nenosiri', 'Reset your password'),
    subtitle: _authText(
      context,
      'Tutakutumia maelekezo ya kuweka nenosiri jipya.',
      'We will send instructions to reset your password.',
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        TextField(
          controller: _email,
          keyboardType: TextInputType.emailAddress,
          decoration: InputDecoration(
            labelText: _authText(context, 'Barua pepe', 'Email'),
            prefixIcon: const Icon(Icons.mail_outline),
          ),
        ),
        if (_error != null) _AuthError(message: _error!),
        if (_sent)
          Padding(
            padding: const EdgeInsets.only(top: 14),
            child: Text(
              _authText(context, 'Ombi limetumwa ikiwa akaunti ipo.', 'If the account exists, reset instructions were sent.'),
              style: TextStyle(color: Theme.of(context).colorScheme.primary),
            ),
          ),
        const SizedBox(height: 16),
        FilledButton(
          onPressed: _sending || _email.text.trim().isEmpty ? null : _submit,
          child: _sending
              ? const SizedBox.square(
                  dimension: 20,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : Text(_authText(context, 'Tuma maelekezo', 'Send instructions')),
        ),
        TextButton(
          onPressed: () => context.go('/login'),
          child: Text(_authText(context, 'Rudi kuingia', 'Back to sign in')),
        ),
      ],
    ),
  );
}

class ResetPasswordPage extends ConsumerStatefulWidget {
  const ResetPasswordPage({super.key});

  @override
  ConsumerState<ResetPasswordPage> createState() => _ResetPasswordPageState();
}

class _ResetPasswordPageState extends ConsumerState<ResetPasswordPage> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _token = TextEditingController();
  final _password = TextEditingController();
  bool _sending = false;
  String? _error;
  bool _complete = false;

  @override
  void dispose() {
    _email.dispose();
    _token.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _sending = true;
      _error = null;
    });
    try {
      await ref.read(landFocusApiProvider).resetPassword(
        email: _email.text,
        token: _token.text,
        password: _password.text,
      );
      if (mounted) setState(() => _complete = true);
    } on LandFocusApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) => _AuthPageFrame(
    title: _authText(context, 'Weka nenosiri jipya', 'Choose a new password'),
    subtitle: _authText(context, 'Tumia kiungo au msimbo uliotumwa kwako.', 'Enter the reset token sent to your email.'),
    child: Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _authInput(context, _email, _authText(context, 'Barua pepe', 'Email'), Icons.mail_outline),
          const SizedBox(height: 12),
          _authInput(context, _token, _authText(context, 'Msimbo wa kurejesha', 'Reset token'), Icons.key_outlined),
          const SizedBox(height: 12),
          TextFormField(
            controller: _password,
            obscureText: true,
            decoration: InputDecoration(
              labelText: _authText(context, 'Nenosiri jipya', 'New password'),
              prefixIcon: const Icon(Icons.lock_outline),
            ),
            validator: (value) => value == null || value.length < 8
                ? _authText(context, 'Tumia angalau herufi 8.', 'Use at least 8 characters.')
                : null,
          ),
          if (_error != null) _AuthError(message: _error!),
          if (_complete)
            Text(_authText(context, 'Nenosiri limebadilishwa.', 'Password updated.')),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _sending ? null : _submit,
            child: _sending
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : Text(_authText(context, 'Badilisha nenosiri', 'Update password')),
          ),
        ],
      ),
    ),
  );
}

class _AuthPageFrame extends StatelessWidget {
  const _AuthPageFrame({
    required this.title,
    required this.subtitle,
    required this.child,
  });

  final String title;
  final String subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) => Scaffold(
    appBar: AppBar(
      leading: IconButton(
        tooltip: MaterialLocalizations.of(context).backButtonTooltip,
        onPressed: () => context.canPop() ? context.pop() : context.go('/home'),
        icon: const Icon(Icons.arrow_back),
      ),
    ),
    body: SafeArea(
      child: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(22, 16, 22, 32),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 460),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Image.asset(
                    'assets/images/landfocus-logo.png',
                    width: 56,
                    height: 56,
                  ),
                ),
                const SizedBox(height: 22),
                Text(
                  title,
                  textAlign: TextAlign.center,
                  style: Theme.of(context)
                      .textTheme
                      .headlineSmall
                      ?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 8),
                Text(
                  subtitle,
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                    height: 1.45,
                  ),
                ),
                const SizedBox(height: 26),
                child,
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

class _AuthError extends StatelessWidget {
  const _AuthError({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) => Container(
    margin: const EdgeInsets.only(top: 10),
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: Theme.of(context).colorScheme.errorContainer,
      borderRadius: BorderRadius.circular(8),
    ),
    child: Text(
      message,
      style: TextStyle(color: Theme.of(context).colorScheme.onErrorContainer),
    ),
  );
}

Widget _authInput(
  BuildContext context,
  TextEditingController controller,
  String label,
  IconData icon,
) => TextFormField(
  controller: controller,
  decoration: InputDecoration(labelText: label, prefixIcon: Icon(icon)),
  validator: (value) => value == null || value.trim().isEmpty
      ? _authText(context, 'Sehemu hii inahitajika.', 'This field is required.')
      : null,
);

String _errorMessage(Object? error) => error is Error
    ? error.toString()
    : error?.toString() ?? 'Request failed. Please try again.';