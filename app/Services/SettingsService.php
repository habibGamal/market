<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting by key with optional caching
     *
     * @param string|SettingKey $key
     * @param mixed $default
     * @param bool $useCache
     * @return mixed
     */
    public function get(string|SettingKey $key, mixed $default = null, bool $useCache = true): mixed
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        if ($useCache) {
            return Cache::remember("settings.$key", 60 * 60, function () use ($key, $default) {
                return Setting::get($key, $default);
            });
        }

        return Setting::get($key, $default);
    }

    /**
     * Set a setting value
     *
     * @param string|SettingKey $key
     * @param mixed $value
     * @param string|null $type
     * @return void
     */
    public function set(string|SettingKey $key, mixed $value, ?string $type = null): void
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;

            if ($type === null) {
                foreach (SettingKey::cases() as $settingKey) {
                    if ($settingKey->value === $key) {
                        $type = $settingKey->getType();
                        break;
                    }
                }
            }
        }

        Setting::set($key, $value, $type);

        // Clear the cache for this key
        Cache::forget("settings.$key");
    }

    /**
     * Check if a setting exists
     *
     * @param string|SettingKey $key
     * @return bool
     */
    public function has(string|SettingKey $key): bool
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting
     *
     * @param string|SettingKey $key
     * @return void
     */
    public function delete(string|SettingKey $key): void
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        Setting::where('key', $key)->delete();

        // Clear the cache for this key
        Cache::forget("settings.$key");
    }

    /**
     * Get all settings
     *
     * @param string|null $group
     * @return array
     */
    public function all(?string $group = null): array
    {
        $query = Setting::query();

        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->get();

        $result = [];
        foreach ($settings as $setting) {
            $value = Setting::castValue($setting->value, $setting->type);
            $result[$setting->key] = $value;
        }

        return $result;
    }
}
