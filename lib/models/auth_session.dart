class AuthSession {
  const AuthSession({
    required this.token,
    required this.userId,
    required this.name,
    required this.email,
    this.phone,
    this.role,
    this.avatarUrl,
  });

  factory AuthSession.fromJson(Map<String, dynamic> json) {
    final user = json['user'] is Map<String, dynamic>
        ? json['user'] as Map<String, dynamic>
        : json;
    final token = json['access_token'] as String? ?? json['token'] as String?;
    if (token == null || token.isEmpty) {
      throw const FormatException('The server did not return an access token.');
    }

    return AuthSession(
      token: token,
      userId: (user['id'] ?? '').toString(),
      name: user['name'] as String? ?? '',
      email: user['email'] as String? ?? '',
      phone: user['phone'] as String?,
      role: user['role'] as String?,
      avatarUrl: user['avatar_url'] as String? ?? user['avatar'] as String?,
    );
  }

  factory AuthSession.fromStorageJson(Map<String, dynamic> json) => AuthSession(
    token: json['token'] as String,
    userId: json['userId'] as String? ?? '',
    name: json['name'] as String? ?? '',
    email: json['email'] as String? ?? '',
    phone: json['phone'] as String?,
    role: json['role'] as String?,
    avatarUrl: json['avatarUrl'] as String?,
  );

  final String token;
  final String userId;
  final String name;
  final String email;
  final String? phone;
  final String? role;
  final String? avatarUrl;

  Map<String, dynamic> toStorageJson() => {
    'token': token,
    'userId': userId,
    'name': name,
    'email': email,
    'phone': phone,
    'role': role,
    'avatarUrl': avatarUrl,
  };
}