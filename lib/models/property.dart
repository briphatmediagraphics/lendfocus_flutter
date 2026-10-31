class Property {
  const Property({
    required this.id,
    required this.title,
    required this.location,
    required this.type,
    required this.status,
    required this.price,
    required this.currency,
    required this.images,
    this.description,
    this.areaSqm,
    this.bedrooms,
    this.bathrooms,
    this.featured = false,
  });

  factory Property.fromJson(Map<String, dynamic> json) {
    return Property(
      id: json['id'] as String,
      title: json['title'] as String? ?? '',
      location: json['location'] as String? ?? '',
      type: json['property_type'] as String? ?? 'land',
      status: json['status'] as String? ?? 'available',
      price: _number(json['price']),
      currency: json['currency'] as String? ?? 'TZS',
      images: (json['images'] as List<dynamic>? ?? const [])
          .whereType<String>()
          .where((image) => image.isNotEmpty)
          .toList(growable: false),
      description: json['description'] as String?,
      areaSqm: _nullableNumber(json['area_sqm']),
      bedrooms: _nullableNumber(json['bedrooms'])?.toInt(),
      bathrooms: _nullableNumber(json['bathrooms'])?.toInt(),
      featured: json['featured'] as bool? ?? false,
    );
  }

  final String id;
  final String title;
  final String location;
  final String type;
  final String status;
  final double price;
  final String currency;
  final List<String> images;
  final String? description;
  final double? areaSqm;
  final int? bedrooms;
  final int? bathrooms;
  final bool featured;

  static double _number(dynamic value) => _nullableNumber(value) ?? 0;

  static double? _nullableNumber(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }
}
