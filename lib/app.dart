import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:url_launcher/url_launcher.dart';

import 'models/property.dart';
import 'services/landfocus_api.dart';

String tr(BuildContext context, String swahili, String english) =>
    Localizations.localeOf(context).languageCode == 'sw' ? swahili : english;

class LandFocusApp extends StatefulWidget {
  const LandFocusApp({super.key, required this.api});

  final LandFocusApi api;

  @override
  State<LandFocusApp> createState() => _LandFocusAppState();
}

class _LandFocusAppState extends State<LandFocusApp> {
  Locale _locale = const Locale('sw');

  void _toggleLanguage() {
    setState(() {
      _locale = Locale(_locale.languageCode == 'sw' ? 'en' : 'sw');
    });
  }

  @override
  Widget build(BuildContext context) {
    const brand = Color(0xFF17457A);
    return MaterialApp(
      title: 'LandFocus',
      debugShowCheckedModeBanner: false,
      locale: _locale,
      supportedLocales: const [Locale('sw'), Locale('en')],
      localizationsDelegates: GlobalMaterialLocalizations.delegates,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: brand,
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: Colors.white,
        useMaterial3: true,
        textTheme: ThemeData.light().textTheme.apply(
          bodyColor: const Color(0xFF252B35),
          displayColor: const Color(0xFF252B35),
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.transparent,
          centerTitle: false,
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFFE4E8EE)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFFE4E8EE)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: brand, width: 1.5),
          ),
        ),
      ),
      home: HomeShell(
        api: widget.api,
        locale: _locale,
        onToggleLanguage: _toggleLanguage,
      ),
    );
  }
}

class HomeShell extends StatefulWidget {
  const HomeShell({
    super.key,
    required this.api,
    required this.locale,
    required this.onToggleLanguage,
  });

  final LandFocusApi api;
  final Locale locale;
  final VoidCallback onToggleLanguage;

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _selectedTab = 0;

  void _openProperty(Property property) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => PropertyDetailPage(
          api: widget.api,
          propertyId: property.id,
          onBook: (id) => _openBooking(propertyId: id),
        ),
      ),
    );
  }

  void _openBooking({String? propertyId}) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) =>
            BookingPage(api: widget.api, initialPropertyId: propertyId),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.sizeOf(context).width > 800;
    final labels = [
      tr(context, 'Nyumbani', 'Home'),
      tr(context, 'Miradi', 'Properties'),
      tr(context, 'Miadi', 'Book'),
      tr(context, 'Wasiliana', 'Contact'),
    ];
    final tabs = [
      HomeTab(
        api: widget.api,
        onBrowse: () => setState(() => _selectedTab = 1),
        onContact: () => setState(() => _selectedTab = 3),
        onOpenProperty: _openProperty,
      ),
      PropertiesTab(api: widget.api, onOpenProperty: _openProperty),
      BookingPage(api: widget.api),
      ContactPage(api: widget.api),
    ];

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 18,
        title: const Row(
          children: [
            Image(
              image: AssetImage('assets/images/landfocus-logo.png'),
              width: 34,
              height: 34,
            ),
            SizedBox(width: 7),
            Text.rich(
              TextSpan(
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 18,
                ),
                children: [
                  TextSpan(
                    text: 'Land',
                    style: TextStyle(color: Color(0xFF252B35)),
                  ),
                  TextSpan(
                    text: 'Focus',
                    style: TextStyle(color: Color(0xFF17457A)),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          if (isWide)
            for (var index = 0; index < labels.length; index++)
              TextButton(
                onPressed: () => setState(() => _selectedTab = index),
                child: Text(
                  labels[index],
                  style: TextStyle(
                    color: _selectedTab == index
                        ? const Color(0xFF17457A)
                        : const Color(0xFF626B78),
                    fontWeight: _selectedTab == index
                        ? FontWeight.w700
                        : FontWeight.w500,
                  ),
                ),
              ),
          TextButton(
            onPressed: widget.onToggleLanguage,
            child: Text(widget.locale.languageCode == 'sw' ? 'EN' : 'SW'),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: IndexedStack(index: _selectedTab, children: tabs),
      bottomNavigationBar: isWide
          ? null
          : NavigationBar(
              backgroundColor: Colors.white,
              indicatorColor: const Color(0xFFEAF1F9),
              selectedIndex: _selectedTab,
              onDestinationSelected: (index) =>
                  setState(() => _selectedTab = index),
              destinations: [
                NavigationDestination(
                  icon: const Icon(Icons.home_outlined),
                  selectedIcon: const Icon(Icons.home),
                  label: labels[0],
                ),
                NavigationDestination(
                  icon: const Icon(Icons.landscape_outlined),
                  selectedIcon: const Icon(Icons.landscape),
                  label: labels[1],
                ),
                NavigationDestination(
                  icon: const Icon(Icons.event_outlined),
                  selectedIcon: const Icon(Icons.event),
                  label: labels[2],
                ),
                NavigationDestination(
                  icon: const Icon(Icons.chat_bubble_outline),
                  selectedIcon: const Icon(Icons.chat_bubble),
                  label: labels[3],
                ),
              ],
            ),
    );
  }
}

class HomeTab extends StatefulWidget {
  const HomeTab({
    super.key,
    required this.api,
    required this.onBrowse,
    required this.onContact,
    required this.onOpenProperty,
  });

  final LandFocusApi api;
  final VoidCallback onBrowse;
  final VoidCallback onContact;
  final ValueChanged<Property> onOpenProperty;

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  late Future<List<Property>> _properties;
  late Future<List<Map<String, dynamic>>> _services;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _properties = widget.api.properties(limit: 6);
    _services = widget.api.services();
  }

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    final columns = width > 1000
        ? 3
        : width > 620
        ? 2
        : 1;
    return RefreshIndicator(
      onRefresh: () async => setState(_load),
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          const SizedBox(height: 8),
          Padding(
            padding: EdgeInsets.zero,
            child: Container(
              constraints: const BoxConstraints(minHeight: 390),
              clipBehavior: Clip.antiAlias,
              decoration: BoxDecoration(borderRadius: BorderRadius.zero),
              child: Stack(
                children: [
                  const Positioned.fill(
                    child: Image(
                      image: AssetImage('assets/images/hero-landfocus.jpg'),
                      fit: BoxFit.cover,
                    ),
                  ),
                  Positioned.fill(
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            const Color(0xFF142F54).withValues(alpha: .94),
                            const Color(0xFF245F9D).withValues(alpha: .72),
                            Colors.black.withValues(alpha: .32),
                          ],
                          begin: Alignment.bottomLeft,
                          end: Alignment.topRight,
                        ),
                      ),
                    ),
                  ),
                  Padding(
                    padding: EdgeInsets.symmetric(
                      horizontal: width > 800 ? 64 : 24,
                      vertical: width > 800 ? 64 : 36,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 7,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: .14),
                            borderRadius: BorderRadius.circular(30),
                          ),
                          child: Text(
                            tr(
                              context,
                              'Mshirika wako wa ardhi Tanzania',
                              'Your trusted land partner in Tanzania',
                            ),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                            ),
                          ),
                        ),
                        const SizedBox(height: 22),
                        Text(
                          tr(
                            context,
                            'Pata Ardhi, Nyumba na Mashamba kwa Urahisi',
                            'Find Land, Homes and Farms with Confidence',
                          ),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 31,
                            height: 1.12,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -.6,
                          ),
                        ),
                        const SizedBox(height: 14),
                        Text(
                          tr(
                            context,
                            'Tunakuunganisha na miradi iliyochaguliwa pamoja na huduma za kitaalamu za ardhi kote Tanzania.',
                            'Explore selected properties and professional land services across Tanzania.',
                          ),
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: .88),
                            fontSize: 15,
                            height: 1.5,
                          ),
                        ),
                        const SizedBox(height: 23),
                        Wrap(
                          spacing: 10,
                          runSpacing: 10,
                          children: [
                            FilledButton.icon(
                              onPressed: widget.onBrowse,
                              style: FilledButton.styleFrom(
                                backgroundColor: Colors.white,
                                foregroundColor: const Color(0xFF17457A),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 18,
                                  vertical: 14,
                                ),
                              ),
                              icon: const Icon(Icons.arrow_forward_rounded),
                              label: Text(
                                tr(
                                  context,
                                  'Tazama miradi',
                                  'Explore properties',
                                ),
                              ),
                            ),
                            OutlinedButton(
                              onPressed: widget.onContact,
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.white,
                                side: BorderSide(
                                  color: Colors.white.withValues(alpha: .55),
                                ),
                                backgroundColor: Colors.white.withValues(
                                  alpha: .1,
                                ),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 18,
                                  vertical: 14,
                                ),
                              ),
                              child: Text(
                                tr(context, 'Wasiliana nasi', 'Contact us'),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          _SectionHeading(
            title: tr(context, 'Miradi inayopatikana', 'Available properties'),
            action: tr(context, 'Tazama zote', 'See all'),
            onAction: widget.onBrowse,
          ),
          FutureBuilder<List<Property>>(
            future: _properties,
            builder: (context, snapshot) {
              if (snapshot.hasError) {
                return _ErrorPanel(
                  message: snapshot.error.toString(),
                  onRetry: () => setState(_load),
                );
              }
              if (!snapshot.hasData) return const _LoadingPanel();
              final properties = snapshot.data!;
              if (properties.isEmpty) {
                return _EmptyPanel(
                  message: tr(
                    context,
                    'Hakuna miradi iliyoorodheshwa kwa sasa.',
                    'No properties are listed right now.',
                  ),
                );
              }
              return _PropertyGrid(
                properties: properties,
                columns: columns,
                onTap: widget.onOpenProperty,
              );
            },
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 30, 20, 6),
            child: Text(
              tr(context, 'Huduma zetu', 'Our services'),
              style: Theme.of(context).textTheme.headlineSmall
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Text(
              tr(
                context,
                'Msaada wa kitaalamu kwa safari yako ya ardhi na mali.',
                'Professional support for your land and property journey.',
              ),
              style: TextStyle(color: Colors.grey.shade700),
            ),
          ),
          FutureBuilder<List<Map<String, dynamic>>>(
            future: _services,
            builder: (context, snapshot) {
              if (snapshot.hasError) {
                return _ErrorPanel(
                  message: snapshot.error.toString(),
                  onRetry: () => setState(_load),
                );
              }
              if (!snapshot.hasData) return const _LoadingPanel();
              final services = snapshot.data!.take(8).toList();
              if (services.isEmpty) {
                return _EmptyPanel(
                  message: tr(
                    context,
                    'Huduma hazipatikani kwa sasa.',
                    'Services are unavailable right now.',
                  ),
                );
              }
              return Padding(
                padding: const EdgeInsets.all(18),
                child: Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  children: services.map((service) {
                    final language = Localizations.localeOf(context)
                        .languageCode;
                    final title = _localized(service['title'], language);
                    final description = _localized(
                      service['description'],
                      language,
                    );
                    return SizedBox(
                      width: width > 900
                          ? (width - 80) / 3
                          : width > 600
                          ? (width - 64) / 2
                          : width - 36,
                      child: Card(
                        elevation: 0,
                        color: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(18),
                          side: const BorderSide(color: Color(0xFFE7ECE8)),
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(18),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(
                                Icons.verified_outlined,
                                color: Color(0xFF17457A),
                              ),
                              const SizedBox(height: 10),
                              Text(
                                title,
                                style: const TextStyle(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                description,
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Colors.grey.shade700,
                                  height: 1.4,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              );
            },
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 42, 20, 18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tr(context, 'Kwa nini LandFocus?', 'Why LandFocus?'),
                  style: Theme.of(context).textTheme.headlineSmall
                      ?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 8),
                Text(
                  tr(
                    context,
                    'Tumejitolea kuhakikisha kila mteja anapata mradi kwa urahisi, usalama na bei nzuri.',
                    'We help every client find the right property with confidence, security and fair pricing.',
                  ),
                  style: TextStyle(color: Colors.grey.shade700, height: 1.5),
                ),
                const SizedBox(height: 20),
                _TrustPoint(
                  icon: Icons.verified_user_outlined,
                  title: tr(context, 'Hati Halali', 'Verified documents'),
                  description: tr(
                    context,
                    'Miradi yenye nyaraka kamili na zilizothibitishwa.',
                    'Properties with complete, verified documentation.',
                  ),
                ),
                _TrustPoint(
                  icon: Icons.handshake_outlined,
                  title: tr(context, 'Huduma ya karibu', 'Personal support'),
                  description: tr(
                    context,
                    'Tunakusaidia hatua kwa hatua hadi upate mradi wako.',
                    'We guide you at every step until you find your property.',
                  ),
                ),
                _TrustPoint(
                  icon: Icons.workspace_premium_outlined,
                  title: tr(context, 'Uzoefu wa miaka', 'Years of experience'),
                  description: tr(
                    context,
                    'Tumesaidia mamia ya wateja kupata miradi wanayohitaji.',
                    'We have helped hundreds of clients find the right property.',
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 34),
              decoration: BoxDecoration(
                color: const Color(0xFF17457A),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                children: [
                  Text(
                    tr(
                      context,
                      'Tayari kupata mradi wako?',
                      'Ready to find your property?',
                    ),
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    tr(
                      context,
                      'Tuma ombi lako sasa na timu yetu itakusaidia kupata unachohitaji.',
                      'Send a request and our team will help you find what you need.',
                    ),
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white70, height: 1.5),
                  ),
                  const SizedBox(height: 18),
                  FilledButton(
                    onPressed: widget.onContact,
                    style: FilledButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF17457A),
                    ),
                    child: Text(tr(context, 'Anza sasa', 'Get started')),
                  ),
                ],
              ),
            ),
          ),
          const _SiteFooter(),
          const SizedBox(height: 25),
        ],
      ),
    );
  }
}

String _localized(dynamic value, String language) {
  if (value is Map<String, dynamic>) return value[language] as String? ?? '';
  return '';
}

class PropertiesTab extends StatefulWidget {
  const PropertiesTab({
    super.key,
    required this.api,
    required this.onOpenProperty,
  });

  final LandFocusApi api;
  final ValueChanged<Property> onOpenProperty;

  @override
  State<PropertiesTab> createState() => _PropertiesTabState();
}

class _PropertiesTabState extends State<PropertiesTab> {
  String? _type;
  late Future<List<Property>> _properties;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _properties = widget.api.properties(type: _type);
  }

  @override
  Widget build(BuildContext context) {
    final filters = <(String?, String)>[
      (null, tr(context, 'Zote', 'All')),
      ('land', tr(context, 'Ardhi', 'Land')),
      ('house', tr(context, 'Nyumba', 'House')),
      ('farm', tr(context, 'Mashamba', 'Farms')),
      ('office', tr(context, 'Ofisi', 'Offices')),
    ];
    final width = MediaQuery.sizeOf(context).width;
    final columns = width > 1000
        ? 3
        : width > 620
        ? 2
        : 1;
    return ListView(
      padding: EdgeInsets.zero,
      children: [
        Container(
          color: const Color(0xFFF1F5FA),
          padding: const EdgeInsets.fromLTRB(20, 22, 20, 18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                tr(context, 'Miradi inayopatikana', 'Available properties'),
                style: Theme.of(context).textTheme.headlineSmall
                    ?.copyWith(fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 5),
              Text(
                tr(
                  context,
                  'Chagua aina ya mradi unaoutafuta.',
                  'Filter by the type of property you need.',
                ),
                style: TextStyle(color: Colors.grey.shade700),
              ),
              const SizedBox(height: 15),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: filters
                      .map(
                        (filter) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: Text(filter.$2),
                            selected: _type == filter.$1,
                            onSelected: (_) => setState(() {
                              _type = filter.$1;
                              _load();
                            }),
                          ),
                        ),
                      )
                      .toList(),
                ),
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(0, 16, 0, 24),
          child: FutureBuilder<List<Property>>(
            future: _properties,
            builder: (context, snapshot) {
              if (snapshot.hasError) {
                return _ErrorPanel(
                  message: snapshot.error.toString(),
                  onRetry: () => setState(_load),
                );
              }
              if (!snapshot.hasData) return const _LoadingPanel();
              if (snapshot.data!.isEmpty) {
                return _EmptyPanel(
                  message: tr(
                    context,
                    'Hakuna miradi iliyopatikana kwa kichujio hiki.',
                    'No properties found for this filter.',
                  ),
                );
              }
              return _PropertyGrid(
                properties: snapshot.data!,
                columns: columns,
                onTap: widget.onOpenProperty,
              );
            },
          ),
        ),
        const _SiteFooter(),
      ],
    );
  }
}

class PropertyDetailPage extends StatefulWidget {
  const PropertyDetailPage({
    super.key,
    required this.api,
    required this.propertyId,
    required this.onBook,
  });

  final LandFocusApi api;
  final String propertyId;
  final ValueChanged<String> onBook;

  @override
  State<PropertyDetailPage> createState() => _PropertyDetailPageState();
}

class _PropertyDetailPageState extends State<PropertyDetailPage> {
  late Future<Property> _property;

  @override
  void initState() {
    super.initState();
    _property = widget.api.property(widget.propertyId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(tr(context, 'Maelezo ya mradi', 'Property details')),
      ),
      body: FutureBuilder<Property>(
        future: _property,
        builder: (context, snapshot) {
          if (snapshot.hasError) {
            return _ErrorPanel(
              message: snapshot.error.toString(),
              onRetry: () => setState(
                () => _property = widget.api.property(widget.propertyId),
              ),
            );
          }
          if (!snapshot.hasData) return const _LoadingPanel();
          final property = snapshot.data!;
          return ListView(
            padding: const EdgeInsets.fromLTRB(18, 8, 18, 30),
            children: [
              _PropertyImage(
                url: property.images.isEmpty ? null : property.images.first,
                fallbackAsset: _fallbackFor(property.type),
                height: 250,
              ),
              const SizedBox(height: 18),
              Chip(label: Text(_typeLabel(context, property.type))),
              Text(
                property.title,
                style: Theme.of(context).textTheme.headlineMedium
                    ?.copyWith(fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.location_on_outlined, size: 18),
                  const SizedBox(width: 4),
                  Expanded(child: Text(property.location)),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                '${property.currency} ${_formatPrice(property.price)}',
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: const Color(0xFF17457A),
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 14),
              Wrap(
                spacing: 10,
                children: [
                  if (property.areaSqm != null)
                    Chip(
                      avatar: const Icon(Icons.square_foot),
                      label: Text('${property.areaSqm} m²'),
                    ),
                  if (property.bedrooms != null && property.bedrooms! > 0)
                    Chip(
                      avatar: const Icon(Icons.bed_outlined),
                      label: Text('${property.bedrooms}'),
                    ),
                  if (property.bathrooms != null && property.bathrooms! > 0)
                    Chip(
                      avatar: const Icon(Icons.bathtub_outlined),
                      label: Text('${property.bathrooms}'),
                    ),
                ],
              ),
              if (property.description?.isNotEmpty ?? false) ...[
                const SizedBox(height: 12),
                Text(
                  tr(context, 'Maelezo', 'Description'),
                  style: Theme.of(context).textTheme.titleLarge
                      ?.copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 6),
                Text(
                  property.description!,
                  style: const TextStyle(height: 1.5),
                ),
              ],
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () =>
                    _showInquiryForm(context, widget.api, property),
                icon: const Icon(Icons.chat_outlined),
                label: Text(
                  tr(
                    context,
                    'Ulizia kuhusu mradi huu',
                    'Ask about this property',
                  ),
                ),
              ),
              const SizedBox(height: 8),
              OutlinedButton.icon(
                onPressed: () => widget.onBook(property.id),
                icon: const Icon(Icons.event_available_outlined),
                label: Text(
                  tr(context, 'Panga ziara ya eneo', 'Book a site visit'),
                ),
              ),
              TextButton.icon(
                onPressed: () => _openUrl(
                  context,
                  'https://wa.me/255667999046?text=${Uri.encodeComponent('LandFocus: ${property.title}')}',
                ),
                icon: const Icon(Icons.open_in_new),
                label: Text(
                  tr(context, 'Wasiliana WhatsApp', 'Contact on WhatsApp'),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

Future<void> _showInquiryForm(
  BuildContext context,
  LandFocusApi api,
  Property property,
) async {
  await showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    builder: (_) => InquiryForm(api: api, property: property),
  );
}

class InquiryForm extends StatefulWidget {
  const InquiryForm({super.key, required this.api, this.property});

  final LandFocusApi api;
  final Property? property;

  @override
  State<InquiryForm> createState() => _InquiryFormState();
}

class _InquiryFormState extends State<InquiryForm> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  late final TextEditingController _message = TextEditingController(
    text: widget.property == null
        ? ''
        : 'Ninaomba maelezo zaidi kuhusu: ${widget.property!.title}',
  );
  bool _sending = false;
  bool _sent = false;
  String? _error;

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _email.dispose();
    _message.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _sending = true;
      _error = null;
    });
    try {
      await widget.api.submitInquiry(
        name: _name.text,
        phone: _phone.text,
        email: _email.text,
        message: _message.text,
        propertyId: widget.property?.id,
      );
      if (mounted) setState(() => _sent = true);
    } on LandFocusApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
        20,
        18,
        20,
        MediaQuery.viewInsetsOf(context).bottom + 20,
      ),
      child: _sent
          ? _SuccessPanel(
              message: tr(
                context,
                'Asante! Ombi lako limepokelewa.',
                'Thank you! Your inquiry has been received.',
              ),
            )
          : Form(
              key: _formKey,
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      tr(context, 'Tuma ombi', 'Send an inquiry'),
                      style: Theme.of(context).textTheme.headlineSmall
                          ?.copyWith(fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 14),
                    _textField(
                      context,
                      _name,
                      tr(context, 'Jina kamili', 'Full name'),
                      requiredField: true,
                    ),
                    _textField(
                      context,
                      _phone,
                      tr(context, 'Namba ya simu', 'Phone number'),
                      requiredField: true,
                      keyboardType: TextInputType.phone,
                    ),
                    _textField(
                      context,
                      _email,
                      tr(context, 'Barua pepe (hiari)', 'Email (optional)'),
                      keyboardType: TextInputType.emailAddress,
                    ),
                    _textField(
                      context,
                      _message,
                      tr(context, 'Ujumbe', 'Message'),
                      requiredField: true,
                      maxLines: 3,
                    ),
                    if (_error != null) _InlineError(message: _error!),
                    const SizedBox(height: 8),
                    FilledButton(
                      onPressed: _sending ? null : _submit,
                      child: _sending
                          ? const CircularProgressIndicator()
                          : Text(tr(context, 'Tuma ombi', 'Send inquiry')),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

class BookingPage extends StatefulWidget {
  const BookingPage({super.key, required this.api, this.initialPropertyId});

  final LandFocusApi api;
  final String? initialPropertyId;

  @override
  State<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends State<BookingPage> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  final _notes = TextEditingController();
  late Future<List<Property>> _properties;
  DateTime? _scheduledAt;
  String _appointmentType = 'site_visit';
  String? _propertyId;
  bool _sending = false;
  bool _sent = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _propertyId = widget.initialPropertyId;
    _properties = widget.api.properties(limit: 50);
  }

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _email.dispose();
    _notes.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final day = await showDatePicker(
      context: context,
      initialDate: now.add(const Duration(days: 1)),
      firstDate: now,
      lastDate: now.add(const Duration(days: 365)),
    );
    if (day == null || !mounted) return;
    final time = await showTimePicker(
      context: context,
      initialTime: const TimeOfDay(hour: 10, minute: 0),
    );
    if (time == null || !mounted) return;
    setState(
      () => _scheduledAt = DateTime(
        day.year,
        day.month,
        day.day,
        time.hour,
        time.minute,
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_scheduledAt == null) {
      setState(
        () => _error = tr(
          context,
          'Chagua tarehe na muda wa miadi.',
          'Choose a date and time for your appointment.',
        ),
      );
      return;
    }
    setState(() {
      _sending = true;
      _error = null;
    });
    try {
      await widget.api.submitAppointment(
        name: _name.text,
        phone: _phone.text,
        email: _email.text,
        appointmentType: _appointmentType,
        scheduledAt: _scheduledAt!,
        propertyId: _propertyId,
        notes: _notes.text,
      );
      if (mounted) setState(() => _sent = true);
    } on LandFocusApiException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_sent) {
      return _SuccessPanel(
        message: tr(
          context,
          'Ombi la miadi limepokelewa. Tutawasiliana nawe kuthibitisha.',
          'Your appointment request was received. We will contact you to confirm.',
        ),
      );
    }
    return ListView(
      padding: const EdgeInsets.fromLTRB(18, 12, 18, 28),
      children: [
        Text(
          tr(context, 'Panga miadi', 'Book an appointment'),
          style: Theme.of(context).textTheme.headlineSmall
              ?.copyWith(fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 6),
        Text(
          tr(
            context,
            'Panga ziara ya eneo au mazungumzo na timu yetu.',
            'Schedule a site visit or consultation with our team.',
          ),
          style: TextStyle(color: Colors.grey.shade700),
        ),
        const SizedBox(height: 18),
        Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 560),
            child: Form(
              key: _formKey,
              child: Column(
                children: [
                  _textField(
                    context,
                    _name,
                    tr(context, 'Jina kamili', 'Full name'),
                    requiredField: true,
                  ),
                  _textField(
                    context,
                    _phone,
                    tr(context, 'Namba ya simu', 'Phone number'),
                    requiredField: true,
                    keyboardType: TextInputType.phone,
                  ),
                  _textField(
                    context,
                    _email,
                    tr(context, 'Barua pepe (hiari)', 'Email (optional)'),
                    keyboardType: TextInputType.emailAddress,
                  ),
                  DropdownButtonFormField<String>(
                    initialValue: _appointmentType,
                    decoration: InputDecoration(
                      labelText: tr(
                        context,
                        'Aina ya miadi',
                        'Appointment type',
                      ),
                    ),
                    items: [
                      DropdownMenuItem(
                        value: 'site_visit',
                        child: Text(tr(context, 'Ziara ya eneo', 'Site visit')),
                      ),
                      DropdownMenuItem(
                        value: 'consultation',
                        child: Text(tr(context, 'Ushauri', 'Consultation')),
                      ),
                    ],
                    onChanged: (value) => setState(
                      () => _appointmentType = value ?? 'site_visit',
                    ),
                  ),
                  const SizedBox(height: 12),
                  FutureBuilder<List<Property>>(
                    future: _properties,
                    builder: (context, snapshot) {
                      if (snapshot.hasError) {
                        return _InlineError(message: snapshot.error.toString());
                      }
                      if (!snapshot.hasData) {
                        return const LinearProgressIndicator();
                      }
                      final properties = snapshot.data!;
                      final selected =
                          properties.any(
                            (property) => property.id == _propertyId,
                          )
                          ? _propertyId
                          : null;
                      return DropdownButtonFormField<String?>(
                        initialValue: selected,
                        decoration: InputDecoration(
                          labelText: tr(
                            context,
                            'Mradi (hiari)',
                            'Property (optional)',
                          ),
                        ),
                        items: [
                          DropdownMenuItem<String?>(
                            value: null,
                            child: Text(
                              tr(
                                context,
                                'Hakuna uliochaguliwa',
                                'No property selected',
                              ),
                            ),
                          ),
                          ...properties.map(
                            (property) => DropdownMenuItem<String?>(
                              value: property.id,
                              child: Text(
                                property.title,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ],
                        onChanged: (value) =>
                            setState(() => _propertyId = value),
                      );
                    },
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: _pickDate,
                    icon: const Icon(Icons.calendar_month_outlined),
                    label: Text(
                      _scheduledAt == null
                          ? tr(
                              context,
                              'Chagua tarehe na muda',
                              'Choose date and time',
                            )
                          : _scheduledAt!.toLocal().toString().substring(0, 16),
                    ),
                  ),
                  const SizedBox(height: 12),
                  _textField(
                    context,
                    _notes,
                    tr(
                      context,
                      'Maelezo ya ziada (hiari)',
                      'Additional notes (optional)',
                    ),
                    maxLines: 3,
                  ),
                  if (_error != null) _InlineError(message: _error!),
                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _sending ? null : _submit,
                      child: _sending
                          ? const CircularProgressIndicator()
                          : Text(
                              tr(
                                context,
                                'Tuma ombi la miadi',
                                'Request appointment',
                              ),
                            ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const _SiteFooter(),
      ],
    );
  }
}

class ContactPage extends StatelessWidget {
  const ContactPage({super.key, required this.api});

  final LandFocusApi api;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    final panelWidth = width > 900 ? (width - 72) / 2 : width - 36;
    return ListView(
      padding: EdgeInsets.zero,
      children: [
        Container(
          width: double.infinity,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF17365D), Color(0xFF245F9D)],
            ),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 34),
          child: Column(
            children: [
              Text(
                tr(context, 'Wasiliana nasi', 'Contact us'),
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                tr(
                  context,
                  'Timu yetu iko tayari kukusaidia kupata mradi unaofaa.',
                  'Our team is ready to help you find the right property.',
                ),
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.white70),
              ),
            ],
          ),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 28),
          child: Align(
            alignment: Alignment.topCenter,
            child: Wrap(
              spacing: 24,
              runSpacing: 24,
              children: [
                SizedBox(
                  width: panelWidth,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        tr(context, 'Njia za kuwasiliana', 'Contact options'),
                        style: Theme.of(context).textTheme.titleLarge
                            ?.copyWith(fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 12),
                      _ContactAction(
                        icon: Icons.phone_outlined,
                        title: tr(context, 'Simu', 'Phone'),
                        subtitle: '+255 667 999 046',
                        url: 'tel:+255667999046',
                      ),
                      _ContactAction(
                        icon: Icons.chat_outlined,
                        title: 'WhatsApp',
                        subtitle: tr(
                          context,
                          'Tuma ujumbe sasa',
                          'Send us a message',
                        ),
                        url: 'https://wa.me/255667999046',
                      ),
                      _ContactAction(
                        icon: Icons.email_outlined,
                        title: tr(context, 'Barua pepe', 'Email'),
                        subtitle: 'info@landfocus.co.tz',
                        url: 'mailto:info@landfocus.co.tz',
                      ),
                      Card(
                        elevation: 0,
                        color: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                          side: const BorderSide(color: Color(0xFFE4E8EE)),
                        ),
                        child: const ListTile(
                          leading: Icon(
                            Icons.location_on_outlined,
                            color: Color(0xFF17457A),
                          ),
                          title: Text('85MP+7M'),
                          subtitle: Text('Dar es Salaam, Tanzania'),
                        ),
                      ),
                    ],
                  ),
                ),
                SizedBox(
                  width: panelWidth,
                  child: Card(
                    elevation: 0,
                    margin: EdgeInsets.zero,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: const BorderSide(color: Color(0xFFE4E8EE)),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            tr(context, 'Tuma ujumbe', 'Send a message'),
                            style: Theme.of(context).textTheme.titleLarge
                                ?.copyWith(fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 16),
                          InquiryForm(api: api),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const _SiteFooter(),
      ],
    );
  }
}

class _ContactAction extends StatelessWidget {
  const _ContactAction({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.url,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final String url;

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      color: Colors.white,
      child: ListTile(
        leading: Icon(icon, color: const Color(0xFF17457A)),
        title: Text(title),
        subtitle: Text(subtitle),
        trailing: const Icon(Icons.open_in_new, size: 18),
        onTap: () => _openUrl(context, url),
      ),
    );
  }
}

class _PropertyGrid extends StatelessWidget {
  const _PropertyGrid({
    required this.properties,
    required this.columns,
    required this.onTap,
  });

  final List<Property> properties;
  final int columns;
  final ValueChanged<Property> onTap;

  @override
  Widget build(BuildContext context) {
    if (columns == 1) {
      return Padding(
        padding: const EdgeInsets.symmetric(horizontal: 18),
        child: Column(
          children: properties
              .map(
                (property) => _PropertyCard(
                  property: property,
                  onTap: () => onTap(property),
                ),
              )
              .toList(),
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 18),
      child: GridView.builder(
        itemCount: properties.length,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: columns,
          crossAxisSpacing: 14,
          mainAxisSpacing: 14,
          childAspectRatio: 0.82,
        ),
        itemBuilder: (context, index) {
          final property = properties[index];
          return _PropertyCard(
            property: property,
            onTap: () => onTap(property),
          );
        },
      ),
    );
  }
}

class _PropertyCard extends StatelessWidget {
  const _PropertyCard({required this.property, required this.onTap});

  final Property property;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 14),
      clipBehavior: Clip.antiAlias,
      elevation: 1,
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            LayoutBuilder(
              builder: (context, constraints) {
                final imageHeight = constraints.maxWidth * .75;
                return SizedBox(
                  height: imageHeight,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      _PropertyImage(
                        url: property.images.isEmpty
                            ? null
                            : property.images.first,
                        fallbackAsset: _fallbackFor(property.type),
                        height: imageHeight,
                      ),
                      Positioned(
                        left: 12,
                        top: 12,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFF17457A),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            _typeLabel(context, property.type),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ),
                      if (property.status != 'available')
                        Positioned(
                          right: 12,
                          top: 12,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xFFB63B3B),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              property.status == 'sold'
                                  ? tr(context, 'Imeuzwa', 'Sold')
                                  : property.status == 'rented'
                                  ? tr(context, 'Imepangwa', 'Rented')
                                  : tr(context, 'Inasubiri', 'Pending'),
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                );
              },
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(15, 13, 15, 15),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    property.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 16,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 15),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text(
                          property.location,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 9),
                  Text(
                    '${property.currency} ${_formatPrice(property.price)}',
                    style: const TextStyle(
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17457A),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PropertyImage extends StatelessWidget {
  const _PropertyImage({
    required this.url,
    required this.fallbackAsset,
    required this.height,
  });

  final String? url;
  final String fallbackAsset;
  final double height;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: height,
      width: double.infinity,
      child: url == null
          ? _fallback()
          : Image.network(
              url!,
              fit: BoxFit.cover,
              errorBuilder: (_, _, _) => _fallback(),
            ),
    );
  }

  Widget _fallback() => Image.asset(
    fallbackAsset,
    fit: BoxFit.cover,
    errorBuilder: (_, _, _) => Container(
      color: const Color(0xFFE3ECE6),
      child: const Center(
        child: Icon(
          Icons.landscape_outlined,
          color: Color(0xFF17457A),
          size: 48,
        ),
      ),
    ),
  );
}

String _fallbackFor(String type) => switch (type) {
  'land' => 'assets/images/sample-land.jpg',
  'house' => 'assets/images/sample-house.jpg',
  'farm' => 'assets/images/sample-farm.jpg',
  _ => 'assets/images/sample-house.jpg',
};

class _TrustPoint extends StatelessWidget {
  const _TrustPoint({
    required this.icon,
    required this.title,
    required this.description,
  });

  final IconData icon;
  final String title;
  final String description;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 16),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: const Color(0xFFEAF1F9),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: const Color(0xFF17457A), size: 21),
        ),
        const SizedBox(width: 13),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
              const SizedBox(height: 3),
              Text(
                description,
                style: TextStyle(color: Colors.grey.shade700, height: 1.4),
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

class _SiteFooter extends StatelessWidget {
  const _SiteFooter();

  @override
  Widget build(BuildContext context) => Container(
    color: const Color(0xFFF3F6FA),
    padding: const EdgeInsets.fromLTRB(22, 28, 22, 18),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text.rich(
          TextSpan(
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18),
            children: [
              TextSpan(
                text: 'Land',
                style: TextStyle(color: Color(0xFF252B35)),
              ),
              TextSpan(
                text: 'Focus',
                style: TextStyle(color: Color(0xFF17457A)),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        Text(
          tr(
            context,
            'Mshirika wako wa kuaminika katika ardhi, nyumba na mashamba nchini Tanzania.',
            'Your trusted partner in land, houses and farms across Tanzania.',
          ),
          style: TextStyle(color: Colors.grey.shade700, height: 1.5),
        ),
        const SizedBox(height: 16),
        const Wrap(
          spacing: 18,
          runSpacing: 10,
          children: [
            _FooterContact(
              icon: Icons.phone_outlined,
              text: '+255 667 999 046',
            ),
            _FooterContact(
              icon: Icons.email_outlined,
              text: 'info@landfocus.co.tz',
            ),
            _FooterContact(
              icon: Icons.location_on_outlined,
              text: 'Dar es Salaam, Tanzania',
            ),
          ],
        ),
        const Divider(height: 28),
        Text(
          '© ${DateTime.now().year} LandFocus. ${tr(context, 'Haki zote zimehifadhiwa.', 'All rights reserved.')}',
          style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
        ),
      ],
    ),
  );
}

class _FooterContact extends StatelessWidget {
  const _FooterContact({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) => Row(
    mainAxisSize: MainAxisSize.min,
    children: [
      Icon(icon, color: const Color(0xFF17457A), size: 16),
      const SizedBox(width: 6),
      Text(text, style: const TextStyle(fontSize: 12)),
    ],
  );
}

class _SectionHeading extends StatelessWidget {
  const _SectionHeading({
    required this.title,
    required this.action,
    required this.onAction,
  });

  final String title;
  final String action;
  final VoidCallback onAction;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 28, 18, 12),
      child: Row(
        children: [
          Expanded(
            child: Text(
              title,
              style: Theme.of(context).textTheme.titleLarge
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
          ),
          TextButton(onPressed: onAction, child: Text(action)),
        ],
      ),
    );
  }
}

class _LoadingPanel extends StatelessWidget {
  const _LoadingPanel();

  @override
  Widget build(BuildContext context) => const Padding(
    padding: EdgeInsets.all(30),
    child: Center(child: CircularProgressIndicator()),
  );
}

class _EmptyPanel extends StatelessWidget {
  const _EmptyPanel({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.all(20),
    child: Card(
      elevation: 0,
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(22),
        child: Center(child: Text(message, textAlign: TextAlign.center)),
      ),
    ),
  );
}

class _ErrorPanel extends StatelessWidget {
  const _ErrorPanel({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.all(20),
    child: Card(
      elevation: 0,
      color: const Color(0xFFFFF1EF),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            const Icon(Icons.cloud_off_outlined, color: Colors.redAccent),
            const SizedBox(height: 8),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 8),
            OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: Text(tr(context, 'Jaribu tena', 'Retry')),
            ),
          ],
        ),
      ),
    ),
  );
}

class _InlineError extends StatelessWidget {
  const _InlineError({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 8),
    child: Text(
      message,
      style: const TextStyle(color: Colors.red, fontSize: 13),
    ),
  );
}

class _SuccessPanel extends StatelessWidget {
  const _SuccessPanel({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.all(30),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.check_circle_outline,
            color: Color(0xFF17457A),
            size: 54,
          ),
          const SizedBox(height: 12),
          Text(
            message,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.titleMedium,
          ),
        ],
      ),
    ),
  );
}

Widget _textField(
  BuildContext context,
  TextEditingController controller,
  String label, {
  bool requiredField = false,
  TextInputType? keyboardType,
  int maxLines = 1,
}) {
  return Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      decoration: InputDecoration(labelText: label),
      validator: requiredField
          ? (value) => value == null || value.trim().isEmpty
                ? tr(
                    context,
                    'Sehemu hii inahitajika',
                    'This field is required',
                  )
                : null
          : null,
    ),
  );
}

String _typeLabel(BuildContext context, String type) => switch (type) {
  'land' => tr(context, 'Ardhi', 'Land'),
  'house' => tr(context, 'Nyumba', 'House'),
  'farm' => tr(context, 'Shamba', 'Farm'),
  'office' => tr(context, 'Ofisi', 'Office'),
  _ => type,
};

String _formatPrice(double price) {
  final value = price.round().toString();
  final result = StringBuffer();
  for (var index = 0; index < value.length; index++) {
    if (index > 0 && (value.length - index) % 3 == 0) result.write(',');
    result.write(value[index]);
  }
  return result.toString();
}

Future<void> _openUrl(BuildContext context, String value) async {
  final uri = Uri.parse(value);
  final opened = await launchUrl(uri, mode: LaunchMode.externalApplication);
  if (!opened && context.mounted) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          tr(
            context,
            'Kiungo hakikuweza kufunguliwa.',
            'Could not open this link.',
          ),
        ),
      ),
    );
  }
}
