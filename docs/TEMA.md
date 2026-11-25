# Sistema de tema claro / oscuro

Fuente única: `public/css/style.css`, sección 1 (tokens). Claro = `:root`; oscuro = `html[data-theme="dark"]` (solo redefine tokens). El switch (`public/js/theme.js`) escribe `html[data-theme]` y `localStorage['kzm-theme']`; `themeHeadTag()` (helpers.php) fija el tema antes del primer paint.

## Reglas
- Ningún color fuera de la sección 1 de `style.css`. En vistas: `var(--token)`.
- Un valor calculado por PHP se pasa como `style="--x:<?= … ?>"` o, si elige entre estados, como clase modificadora (`status-card--success`).
- Gráficos: `kzmChartPalette()`, `kzmChartColor(s)` (`public/js/chart-theme.js`). Exportes PDF/Excel: `kzmPaperColors()` (siempre tema claro).
- Estados: `.status-badge--{success|warning|danger|info|primary}`, `.panel--*`, `.status-card--*`, `.pay-option`.

## Tokens
| Token | Claro | Oscuro |
|---|---|---|
| `--bg-primary` | `#FBF9FE` | `#14101B` |
| `--bg-secondary` | `#F4EFFA` | `#1A1522` |
| `--surface` | `#FFFFFF` | `#1F1929` |
| `--surface-hover` | `#F5EEFC` | `#2A2238` |
| `--surface-sunken` | `#EEE5FB` | `#2B2139` |
| `--surface-selected` | `#E6D8F8` | `#3A2A52` |
| `--overlay` | `rgba(36, 22, 49, .55)` | `rgba(10, 6, 16, .70)` |
| `--text-primary` | `#241631` | `#ECE8F5` |
| `--text-secondary` | `#3A2C4B` | `#CFC6DE` |
| `--text-muted` | `#594B69` | `#B5AAC8` |
| `--text-disabled` | `#8B7F99` | `#8A7F9C` |
| `--text-link` | `#563080` | `#D7B8FF` |
| `--text-link-hover` | `#3D1F5E` | `#EBD5FF` |
| `--border` | `#D9CCEC` | `#3D3250` |
| `--border-strong` | `#857596` | `#8F82A6` |
| `--focus-ring` | `#7B3FB5` | `#C9A2F5` |
| `--primary` | `#633493` | `#8A4BC4` |
| `--primary-hover` | `#4B2670` | `#9355CF` |
| `--primary-active` | `#3D1F5E` | `#9658D2` |
| `--on-primary` | `#FFFFFF` | `#FFFFFF` |
| `--primary-soft` | `#EEE5FB` | `#3A2A52` |
| `--on-primary-soft` | `#3D1F5E` | `#E6D3FF` |
| `--accent` | `#A21CAF` | `#F0ABFC` |
| `--accent-text` | `#86198F` | `#F0ABFC` |
| `--success` | `#11704A` | `#34B37A` |
| `--success-hover` | `#0C5A3B` | `#4CC58D` |
| `--on-success` | `#FFFFFF` | `#052E1B` |
| `--success-bg` | `#E4F7EC` | `#123A2A` |
| `--success-text` | `#14532D` | `#A7F3D0` |
| `--success-border` | `#7BC99B` | `#2F7F5E` |
| `--warning` | `#F4C542` | `#F2C14E` |
| `--warning-hover` | `#E8B420` | `#F7D06E` |
| `--on-warning` | `#352600` | `#2E2200` |
| `--warning-bg` | `#FFF6D9` | `#3D2E0E` |
| `--warning-text` | `#543900` | `#FDE68A` |
| `--warning-border` | `#D9A32B` | `#8F6D1E` |
| `--danger` | `#B42332` | `#F08A93` |
| `--danger-hover` | `#8F1C28` | `#F5A3AA` |
| `--on-danger` | `#FFFFFF` | `#3A0A10` |
| `--danger-bg` | `#FFF0F1` | `#451A21` |
| `--danger-text` | `#7F1D1D` | `#FECDD3` |
| `--danger-border` | `#E7A0A6` | `#A8404C` |
| `--info` | `#176B87` | `#5BB6D6` |
| `--info-hover` | `#11556C` | `#7CC7E3` |
| `--on-info` | `#FFFFFF` | `#06222D` |
| `--info-bg` | `#E5F4FA` | `#12354A` |
| `--info-text` | `#0C4A6E` | `#BAE6FD` |
| `--info-border` | `#8CC4DA` | `#3C7FA3` |
| `--neutral-bg` | `#EEE9F3` | `#2B2139` |
| `--neutral-text` | `#3A2C4B` | `#CFC6DE` |
| `--neutral-border` | `#CFC4DB` | `#4A3D60` |
| `--chat-own-bg` | `#633493` | `#8A4BC4` |
| `--chat-own-text` | `#FFFFFF` | `#FFFFFF` |
| `--chat-other-bg` | `#FFFFFF` | `#2A2238` |
| `--chat-other-text` | `#241631` | `#ECE8F5` |
| `--chat-meta` | `#594B69` | `#B5AAC8` |
| `--navbar-bg` | `#FFFFFF` | `#1A1522` |
| `--sidebar-bg` | `linear-gradient(180deg, #FFFFFF, #F0E8FB)` | `linear-gradient(180deg, #17111F, #251739)` |
| `--sidebar-text` | `#443650` | `#E0D5F0` |
| `--sidebar-text-active` | `#4F287F` | `#FFFFFF` |
| `--sidebar-active-bg` | `#E8DCF8` | `#3A2A52` |
| `--sidebar-accent` | `#8B5CC7` | `#C9A2F5` |
| `--hero-bg` | `linear-gradient(135deg, #1A0F2E 0%, #3B1766 40%, #5B1A70 100%)` | `(igual)` |
| `--on-hero` | `#F6F0FF` | `(igual)` |
| `--on-hero-muted` | `#E3D4F7` | `(igual)` |
| `--hero-cta-bg` | `#FFFFFF` | `(igual)` |
| `--hero-cta-text` | `#3D1F5E` | `(igual)` |
| `--footer-bg` | `linear-gradient(135deg, #1E1429, #2E1A4A)` | `(igual)` |
| `--on-footer` | `#E7DCF7` | `(igual)` |
| `--on-footer-muted` | `#C9BCDD` | `(igual)` |
| `--on-footer-link` | `#E3CCFF` | `(igual)` |
| `--chart-1` | `#633493` | `#B58BEF` |
| `--chart-2` | `#0E7490` | `#4FC3DC` |
| `--chart-3` | `#B45309` | `#F0A04B` |
| `--chart-4` | `#15803D` | `#5FD08F` |
| `--chart-5` | `#BE185D` | `#F472B6` |
| `--chart-6` | `#1D4ED8` | `#7FA8FF` |
| `--chart-7` | `#8A6A00` | `#E8C547` |
| `--chart-8` | `#52525B` | `#A3A3B8` |
| `--chart-success` | `#15803D` | `#5FD08F` |
| `--chart-warning` | `#B45309` | `#F0A04B` |
| `--chart-danger` | `#B91C1C` | `#F08A93` |
| `--chart-info` | `#0E7490` | `#4FC3DC` |
| `--chart-grid` | `#D9CCEC` | `#3D3250` |
| `--chart-text` | `#3A2C4B` | `#CFC6DE` |
| `--shadow-color` | `rgba(99, 52, 147, .18)` | `rgba(0, 0, 0, .5)` |
| `--shadow-sm` | `0 2px 8px var(--shadow-color)` | `(igual)` |
| `--shadow-md` | `0 8px 28px var(--shadow-color)` | `(igual)` |
| `--radius` | `12px` | `(igual)` |
| `--radius-lg` | `18px` | `(igual)` |
| `--icon-filter` | `none` | `invert(1) grayscale(1) brightness(1.6)` |

## Verificar
```
node tools/contraste.js public/css/style.css      # solo fallos
node tools/contraste.js public/css/style.css -v   # todos los pares
```

## Indicadores de chat sin leer (sin tokens nuevos)
- `.chat-badge` (cliente y admin): burbuja con el nº de **mensajes** sin leer (`99+` si pasan de 99). Fondo `--danger`, texto `--on-danger` (6,5:1 claro / 7,1:1 oscuro); contra el fondo del nav ≥3:1.
- Pares verificados en `tools/contraste.js`: `danger` sobre `navbar-bg`, `surface-hover`, `primary-soft`, `sidebar-from`, `sidebar-active-bg`.
- JS: `public/js/chat-notify.js` (nav/sidebar) y `public/js/chat-realtime.js` (chat abierto).

## Layout de mensajería, botones y reservas (sin tokens de color nuevos)
- Variable de medida `--mobilebar-h` (3.25rem): alto de la barra admin móvil; no es un color.
- Chat admin tipo WhatsApp: `.messenger` (lista `.messenger__list` + panel `.messenger__pane`); conversación activa = `--primary-soft` / `--on-primary-soft` con borde `--primary`. Chat embebido: `chat-ver&embed=1` (`body.is-embed`).
- Botones de topbar, sidebar, barra móvil y navbar comparten tamaño (`padding .5rem .75rem`, `min-height 2.25rem`); icono del switch `1.1rem`.
- Reservas del cliente: `.res-grid` (grid auto-fill, 17rem mín.) y `.res-card` (`--surface`, `--border`, `--text-*`).

## Burbujas de chat: entrega y diagnóstico
- `chat-notify.js`, `chat-realtime.js` y `theme.js` se **incrustan** en la página con `inlineJs()` (helpers.php), igual que `cssTag()`: no dependen de ninguna URL.
- Burbuja del cliente: encima de la palabra «Chat» (esquina superior derecha). Admin: a la derecha de «Chats».
- Diagnóstico: añade `?kzmdiag=1` a cualquier URL para ver el panel `.kzm-diag` (tokens `--surface`, `--primary`, `--text-*`); `?kzmdiag=0` lo quita.
