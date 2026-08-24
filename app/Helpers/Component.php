<?php
/**
 * Component — Reusable UI partials for SkillSystem views.
 * Returns HTML strings using the .ss-* design system namespace.
 */

namespace App\Helpers;

class Component
{
    /**
     * Button — the primary button builder.
     * Options: label, icon, variant (primary|secondary|gradient|outline|soft|ghost|light|success|danger|warning), size (sm|md|lg|xs), type (button|submit|a), href, block, loading, id, class, attr
     */
    public static function button(array $opts): string
    {
        $label = $opts['label'] ?? '';
        $icon = $opts['icon'] ?? '';
        $variant = $opts['variant'] ?? 'primary';
        $size = $opts['size'] ?? 'md';
        $type = $opts['type'] ?? 'button';
        $href = $opts['href'] ?? null;
        $block = !empty($opts['block']);
        $loading = !empty($opts['loading']);
        $id = $opts['id'] ?? '';
        $cls = $opts['class'] ?? '';
        $attr = $opts['attr'] ?? '';

        $classes = 'ss-btn ss-btn-' . $variant;
        if ($size !== 'md') $classes .= ' ss-btn-' . $size;
        if ($block) $classes .= ' ss-btn-block';
        $classes .= $cls ? ' ' . $cls : '';

        $iconHtml = $icon ? '<i class="fas ' . htmlspecialchars($icon) . '"></i>' : '';
        $labelHtml = $label ? '<span>' . htmlspecialchars($label) . '</span>' : '';
        $loadingClass = $loading ? ' ss-btn-loading' : '';

        if ($href) {
            return '<a href="' . htmlspecialchars($href) . '" class="' . $classes . $loadingClass . '"' . ($id ? ' id="' . htmlspecialchars($id) . '"' : '') . ($attr ? ' ' . $attr : '') . '>' . $iconHtml . $labelHtml . '</a>';
        }
        return '<button type="' . htmlspecialchars($type) . '" class="' . $classes . $loadingClass . '"' . ($id ? ' id="' . htmlspecialchars($id) . '"' : '') . ($attr ? ' ' . $attr : '') . '>' . $iconHtml . $labelHtml . '</button>';
    }

    /**
     * Stat card with animated counter.
     */
    public static function statCard(array $opts): string
    {
        $icon = $opts['icon'] ?? 'fa-chart-line';
        $label = $opts['label'] ?? '';
        $value = $opts['value'] ?? 0;
        $trend = $opts['trend'] ?? null;
        $trendUp = $opts['trendUp'] ?? true;
        $color = $opts['color'] ?? 'primary';
        $count = $opts['count'] ?? null;

        $valAttr = $count !== null ? ' data-count="' . htmlspecialchars((string)$count) . '"' : '';
        $valDisplay = $count !== null ? '0' : htmlspecialchars((string)$value);

        $trendHtml = '';
        if ($trend !== null) {
            $cls = $trendUp ? 'stat-trend-up' : 'stat-trend-down';
            $trendIcon = $trendUp ? 'fa-arrow-up' : 'fa-arrow-down';
            $trendHtml = '<span class="stat-trend ' . $cls . '"><i class="fas ' . $trendIcon . '"></i> ' . htmlspecialchars($trend) . '</span>';
        }

        return '<div class="ss-stat-card ss-card-hover"><div class="stat-icon bg-soft-' . $color . '"><i class="fas ' . $icon . '"></i></div><div class="stat-value"' . $valAttr . '>' . $valDisplay . '</div><div class="stat-label">' . htmlspecialchars($label) . '</div>' . $trendHtml . '</div>';
    }

    /**
     * Empty state.
     */
    public static function emptyState(array $opts): string
    {
        $icon = $opts['icon'] ?? 'fa-inbox';
        $title = $opts['title'] ?? 'Nothing here yet';
        $desc = $opts['desc'] ?? '';
        $action = $opts['action'] ?? '';
        return '<div class="ss-empty ss-animate-fade-in"><div class="empty-icon"><i class="fas ' . $icon . '"></i></div><h4>' . htmlspecialchars($title) . '</h4><p>' . htmlspecialchars($desc) . '</p>' . $action . '</div>';
    }

    /**
     * Badge.
     */
    public static function badge(string $text, string $color = 'soft', ?string $icon = null): string
    {
        $iconHtml = $icon ? '<i class="fas ' . $icon . '"></i> ' : '';
        return '<span class="ss-badge ss-badge-' . $color . '">' . $iconHtml . htmlspecialchars($text) . '</span>';
    }

    /**
     * Avatar.
     */
    public static function avatar(?string $name, ?string $src = null, string $size = 'md'): string
    {
        $initials = '';
        if ($name) {
            $parts = explode(' ', trim($name));
            foreach (array_slice($parts, 0, 2) as $p) $initials .= strtoupper(substr($p, 0, 1));
        }
        if ($src) {
            return '<div class="ss-avatar ss-avatar-' . $size . '"><img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($name ?? '') . '"></div>';
        }
        return '<div class="ss-avatar ss-avatar-' . $size . '">' . $initials . '</div>';
    }

    /**
     * Progress bar.
     */
    public static function progress(int $value, string $color = 'primary', string $size = ''): string
    {
        $cls = $size ? 'ss-progress-' . $size : '';
        $barCls = '';
        if ($color === 'success') $barCls = 'ss-progress-bar-success';
        elseif ($color === 'warning') $barCls = 'ss-progress-bar-warning';
        elseif ($color === 'danger') $barCls = 'ss-progress-bar-danger';
        return '<div class="ss-progress ' . $cls . '"><div class="ss-progress-bar ' . $barCls . '" style="width:' . max(0, min(100, $value)) . '%"></div></div>';
    }

    /**
     * Page header with breadcrumb and actions.
     */
    public static function pageHeader(string $title, ?string $breadcrumb = null, ?string $actions = null): string
    {
        $bc = $breadcrumb ? '<div class="breadcrumb">' . $breadcrumb . '</div>' : '';
        $act = $actions ? '<div class="ms-auto d-flex gap-2 flex-wrap">' . $actions . '</div>' : '';
        return '<div class="ss-page-header">' . $bc . '<h1>' . htmlspecialchars($title) . '</h1>' . $act . '</div>';
    }

    /**
     * Card.
     */
    public static function card(string $body, ?string $title = null, ?string $footer = null, ?string $class = ''): string
    {
        $h = $title ? '<div class="ss-card-header"><h3>' . htmlspecialchars($title) . '</h3></div>' : '';
        $f = $footer ? '<div class="ss-card-footer">' . $footer . '</div>' : '';
        return '<div class="ss-card ' . $class . '">' . $h . '<div class="ss-card-body">' . $body . '</div>' . $f . '</div>';
    }

    /**
     * Timeline item.
     */
    public static function timelineItem(string $title, string $desc, string $time, string $color = 'primary', string $icon = 'fa-circle'): string
    {
        return '<div class="ss-timeline-item ' . $color . '"><div class="timeline-time">' . htmlspecialchars($time) . '</div><div class="timeline-title"><i class="fas ' . $icon . ' me-1"></i> ' . htmlspecialchars($title) . '</div><div class="timeline-desc">' . htmlspecialchars($desc) . '</div></div>';
    }

    /**
     * Skeleton loader.
     */
    public static function skeleton(string $type = 'list', int $count = 3): string
    {
        $html = '';
        for ($i = 0; $i < $count; $i++) {
            if ($type === 'card') $html .= '<div class="ss-skeleton ss-skeleton-card mb-3"></div>';
            elseif ($type === 'avatar') $html .= '<div class="d-flex gap-2 align-items-center mb-3"><div class="ss-skeleton ss-skeleton-avatar"></div><div class="flex-grow-1"><div class="ss-skeleton ss-skeleton-text w-50"></div><div class="ss-skeleton ss-skeleton-text w-25"></div></div></div>';
            else $html .= '<div class="ss-skeleton ss-skeleton-text w-100 mb-2"></div><div class="ss-skeleton ss-skeleton-text w-75 mb-3"></div>';
        }
        return $html;
    }

    /**
     * Flash message (new design).
     */
    public static function flash(): string
    {
        if (!Flash::has()) return '';
        $type = Session::getFlash('flash_type', 'info');
        $message = Session::getFlash('flash_message', '');
        $icons = ['success' => 'fa-check-circle', 'error' => 'fa-times-circle', 'warning' => 'fa-exclamation-triangle', 'info' => 'fa-info-circle'];
        $icon = $icons[$type] ?? $icons['info'];
        $typeClass = $type === 'error' ? 'danger' : $type;
        return '<div class="ss-alert ss-alert-' . $typeClass . ' ss-animate-fade-down"><i class="fas ' . $icon . ' alert-icon"></i><div class="alert-body">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div><button type="button" class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>';
    }

    /**
     * Form field (floating label).
     */
    public static function floatField(string $name, string $label, string $type = 'text', ?string $value = null, array $opts = []): string
    {
        $req = !empty($opts['required']) ? ' required' : '';
        $id = $opts['id'] ?? $name;
        $placeholder = ' placeholder=" "';
        $extra = $opts['attr'] ?? '';
        $val = $value !== null ? ' value="' . htmlspecialchars($value) . '"' : '';
        return '<div class="ss-form-group ss-float"><input type="' . $type . '" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $val . $placeholder . $req . ' ' . $extra . '><label for="' . htmlspecialchars($id) . '">' . htmlspecialchars($label) . '</label></div>';
    }

    /**
     * Form select (floating label).
     */
    public static function floatSelect(string $name, string $label, array $options, ?string $value = null, array $opts = []): string
    {
        $req = !empty($opts['required']) ? ' required' : '';
        $id = $opts['id'] ?? $name;
        $optHtml = '';
        foreach ($options as $k => $v) {
            $sel = ((string)$value === (string)$k) ? ' selected' : '';
            $optHtml .= '<option value="' . htmlspecialchars($k) . '"' . $sel . '>' . htmlspecialchars($v) . '</option>';
        }
        return '<div class="ss-form-group ss-float"><select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '"' . $req . '><option value=""></option>' . $optHtml . '</select><label for="' . htmlspecialchars($id) . '">' . htmlspecialchars($label) . '</label></div>';
    }

    /**
     * Alert.
     */
    public static function alert(string $message, string $type = 'info', ?string $title = null): string
    {
        $icons = ['success' => 'fa-check-circle', 'warning' => 'fa-exclamation-triangle', 'danger' => 'fa-times-circle', 'info' => 'fa-info-circle'];
        $icon = $icons[$type] ?? $icons['info'];
        $titleHtml = $title ? '<div class="alert-title">' . htmlspecialchars($title) . '</div>' : '';
        return '<div class="ss-alert ss-alert-' . $type . '"><i class="fas ' . $icon . ' alert-icon"></i><div class="alert-body">' . $titleHtml . htmlspecialchars($message) . '</div></div>';
    }
}
