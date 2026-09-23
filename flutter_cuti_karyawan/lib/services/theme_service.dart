import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeService {
  static const String _keyThemeMode = 'theme_mode_is_dark';
  static final ValueNotifier<ThemeMode> themeModeNotifier = ValueNotifier<ThemeMode>(ThemeMode.light);

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final isDark = prefs.getBool(_keyThemeMode) ?? false;
    themeModeNotifier.value = isDark ? ThemeMode.dark : ThemeMode.light;
  }

  static bool get isDarkMode => themeModeNotifier.value == ThemeMode.dark;

  static Future<void> setDarkMode(bool isDark) async {
    themeModeNotifier.value = isDark ? ThemeMode.dark : ThemeMode.light;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyThemeMode, isDark);
  }

  static Future<void> toggleTheme() async {
    await setDarkMode(!isDarkMode);
  }
}
