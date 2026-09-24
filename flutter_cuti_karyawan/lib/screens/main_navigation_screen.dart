import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';
import 'dashboard_screen.dart';
import 'leave_request_screen.dart';
import 'leave_history_screen.dart';
import 'leave_approval_screen.dart';
import 'settings_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;
  final GlobalKey<LeaveHistoryScreenState> _historyKey = GlobalKey<LeaveHistoryScreenState>();

  void _onTabSelected(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  void _onLeaveSubmitted() {
    setState(() {
      _currentIndex = 2; // Switch to Riwayat Cuti Tab
    });
    // Auto-refresh Riwayat Cuti list immediately
    Future.microtask(() {
      _historyKey.currentState?.loadLeaves();
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final canApprove = user?.canApprove ?? false;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final activeColor = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    final List<Widget> pages = [
      DashboardScreen(onNavigateToTab: _onTabSelected),
      LeaveRequestScreen(onLeaveSubmitted: _onLeaveSubmitted),
      LeaveHistoryScreen(key: _historyKey),
      if (canApprove) const LeaveApprovalScreen(),
      const SettingsScreen(),
    ];

    final List<NavigationDestination> destinations = [
      NavigationDestination(
        icon: const Icon(Icons.dashboard_outlined),
        selectedIcon: Icon(Icons.dashboard_rounded, color: activeColor),
        label: 'Beranda',
      ),
      NavigationDestination(
        icon: const Icon(Icons.add_circle_outline_rounded),
        selectedIcon: Icon(Icons.add_circle_rounded, color: activeColor),
        label: 'Ajukan',
      ),
      NavigationDestination(
        icon: const Icon(Icons.history_outlined),
        selectedIcon: Icon(Icons.history_rounded, color: activeColor),
        label: 'Riwayat',
      ),
      if (canApprove)
        NavigationDestination(
          icon: const Icon(Icons.verified_outlined),
          selectedIcon: Icon(Icons.verified_rounded, color: activeColor),
          label: 'Approval',
        ),
      NavigationDestination(
        icon: const Icon(CupertinoIcons.gear),
        selectedIcon: Icon(CupertinoIcons.gear_solid, color: activeColor),
        label: 'Setting',
      ),
    ];

    final screenWidth = MediaQuery.of(context).size.width;
    final isWideScreen = screenWidth >= 800;

    if (isWideScreen) {
      return Scaffold(
        body: Row(
          children: [
            NavigationRail(
              backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
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
                      decoration: BoxDecoration(
                        color: activeColor,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.business_rounded, color: Colors.white, size: 24),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'NAKAKIN',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                        color: isDark ? Colors.white : AppTheme.primaryDark,
                      ),
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
            VerticalDivider(thickness: 1, width: 1, color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
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
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        elevation: 8,
        indicatorColor: activeColor.withOpacity(isDark ? 0.25 : 0.12),
        destinations: destinations,
      ),
    );
  }
}
