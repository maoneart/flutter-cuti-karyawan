import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';
import 'dashboard_screen.dart';
import 'leave_request_screen.dart';
import 'leave_history_screen.dart';
import 'leave_approval_screen.dart';
import 'public_board_screen.dart';
import 'profile_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;

  void _onTabSelected(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final canApprove = user?.canApprove ?? false;

    final List<Widget> pages = [
      DashboardScreen(onNavigateToTab: _onTabSelected),
      const LeaveRequestScreen(),
      const LeaveHistoryScreen(),
      if (canApprove) const LeaveApprovalScreen(),
      const PublicBoardScreen(),
      const ProfileScreen(),
    ];

    final List<NavigationDestination> destinations = [
      const NavigationDestination(
        icon: Icon(Icons.dashboard_outlined),
        selectedIcon: Icon(Icons.dashboard_rounded, color: AppTheme.primary),
        label: 'Beranda',
      ),
      const NavigationDestination(
        icon: Icon(Icons.add_circle_outline_rounded),
        selectedIcon: Icon(Icons.add_circle_rounded, color: AppTheme.primary),
        label: 'Ajukan',
      ),
      const NavigationDestination(
        icon: Icon(Icons.history_outlined),
        selectedIcon: Icon(Icons.history_rounded, color: AppTheme.primary),
        label: 'Riwayat',
      ),
      if (canApprove)
        const NavigationDestination(
          icon: Icon(Icons.verified_outlined),
          selectedIcon: Icon(Icons.verified_rounded, color: AppTheme.primary),
          label: 'Persetujuan',
        ),
      const NavigationDestination(
        icon: Icon(Icons.tv_rounded),
        selectedIcon: Icon(Icons.tv_rounded, color: AppTheme.primary),
        label: 'Papan Live',
      ),
      const NavigationDestination(
        icon: Icon(Icons.person_outline_rounded),
        selectedIcon: Icon(Icons.person_rounded, color: AppTheme.primary),
        label: 'Profil',
      ),
    ];

    // Responsive: If Screen width > 800 (Web / Tablet), show Side Navigation Rail
    final screenWidth = MediaQuery.of(context).size.width;
    final isWideScreen = screenWidth >= 800;

    if (isWideScreen) {
      return Scaffold(
        body: Row(
          children: [
            NavigationRail(
              selectedIndex: _currentIndex.clamp(0, pages.length - 1),
              onDestinationSelected: (index) {
                setState(() {
                  _currentIndex = index;
                });
              },
              labelType: NavigationRailLabelType.all,
              leading: Padding(
                padding: const EdgeInsets.symmetric(vertical: 20),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: const BoxDecoration(
                        color: AppTheme.primary,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.business_rounded, color: Colors.white, size: 24),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'NAKAKIN',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppTheme.primaryDark),
                    ),
                  ],
                ),
              ),
              destinations: destinations
                  .map((d) => NavigationRailDestination(
                        icon: d.icon,
                        selectedIcon: d.selectedIcon,
                        label: Text(d.label, style: const TextStyle(fontSize: 12)),
                      ))
                  .toList(),
            ),
            const VerticalDivider(thickness: 1, width: 1, color: Color(0xFFE2E8F0)),
            Expanded(
              child: IndexedStack(
                index: _currentIndex.clamp(0, pages.length - 1),
                children: pages,
              ),
            ),
          ],
        ),
      );
    }

    // Mobile Bottom Navigation Bar
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex.clamp(0, pages.length - 1),
        children: pages,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex.clamp(0, pages.length - 1),
        onDestinationSelected: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        backgroundColor: Colors.white,
        elevation: 8,
        indicatorColor: AppTheme.primary.withOpacity(0.12),
        destinations: destinations,
      ),
    );
  }
}
