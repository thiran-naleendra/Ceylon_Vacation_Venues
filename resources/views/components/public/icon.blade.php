@props(['name', 'class' => 'size-5'])
<svg {{ $attributes->merge(['class' => $class, 'aria-hidden' => 'true', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }}>
@switch($name)
@case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
@case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
@case('arrow')<path d="M5 12h14m-5-5 5 5-5 5"/>@break
@case('whatsapp')<path d="M20 11.5a8 8 0 0 1-11.9 7l-4.1 1 1.1-4A8 8 0 1 1 20 11.5Z"/><path d="M8.2 8.2c.5 2.7 2.6 4.8 5.3 5.4l1.4-1.2 1.8.8c.1 1.2-.8 2.2-2 2.3-3.8-.1-7-3.3-7.2-7.1.1-1.1 1.1-2 2.2-1.9l.8 1.8-1.2 1.3"/>@break
@case('phone')<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2.1Z"/>@break
@case('mail')<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>@break
@case('pin')<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>@break
@case('users')<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>@break
@case('luggage')<rect x="5" y="7" width="14" height="14" rx="2"/><path d="M9 7V4h6v3M9 11v6M15 11v6"/>@break
@case('snow')<path d="M12 2v20M4.2 6.5l15.6 9M4.2 17.5l15.6-9M8 4l4 3 4-3M8 20l4-3 4 3"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('shield')<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>@break
@case('map')<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/><path d="M9 3v15M15 6v15"/>@break
@case('sparkle')<path d="m12 3 1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3ZM19 16l.7 2.3L22 19l-2.3.7L19 22l-.7-2.3L16 19l2.3-.7L19 16Z"/>@break
@case('home')<path d="m3 11 9-7 9 7M5 10v10h14V10M9 20v-6h6v6"/>@break
@case('facebook')<path d="M14 8h3V4h-3c-3.3 0-5 2-5 5v3H6v4h3v6h4v-6h3.5l.5-4h-4V9c0-.7.3-1 1-1Z"/>@break
@case('instagram')<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>@break
@case('youtube')<path d="M21 8.2a2.8 2.8 0 0 0-2-2C17.2 5.7 12 5.7 12 5.7s-5.2 0-7 .5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2.5 12 29 29 0 0 0 3 15.8a2.8 2.8 0 0 0 2 2c1.8.5 7 .5 7 .5s5.2 0 7-.5a2.8 2.8 0 0 0 2-2 29 29 0 0 0 .5-3.8 29 29 0 0 0-.5-3.8Z"/><path d="m10 15 5-3-5-3v6Z"/>@break
@case('linkedin')<rect x="4" y="9" width="4" height="11"/><path d="M6 4.5v.01M12 20v-6.5a4 4 0 0 1 8 0V20M12 9v11"/>@break
@case('tiktok')<path d="M15 4v11.5a4.5 4.5 0 1 1-4-4.47M15 4c.6 3 2.4 4.8 5 5"/>@break
@case('x')<path d="M4 4l16 16M20 4 4 20"/>@break
@default<circle cx="12" cy="12" r="9"/>@endswitch
</svg>
