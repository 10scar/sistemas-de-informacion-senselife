# Senselife — Design System v1.0

## Contexto del Proyecto

Sistema de diseño oficial para la plataforma **Senselife**, orientada al monitoreo clínico en Unidades de Cuidados Intensivos (UCI).
---

# 1. Principios de Diseño

## Legibilidad Clínica
La prioridad principal es garantizar la lectura rápida y precisa de signos vitales y estados críticos desde cualquier distancia.

## Confianza Institucional
La interfaz utiliza patrones visuales, tipografías y colores ampliamente adoptados en el ecosistema healthcare para reducir la barrera de desconfianza tecnológica.

## Accesibilidad (WCAG 2.1 AA)
- Todos los contrastes cumplen mínimo ratio 4.5:1.
- Los estados semánticos nunca dependen únicamente del color.
- Todo estado crítico debe combinar:
  - Color
  - Ícono
  - Texto
  - Sonido (si aplica)

---

# 2. Sistema Cromático

## 2.1 Tokens Base (Brand)

| Token | HEX | Uso |
|---|---|---|
| `--text` | `#050315` | Texto principal |
| `--background` | `#fbfbfe` | Fondo general |
| `--primary` | `#050677` | Azul institucional |
| `--secondary` | `#dddbff` | Fondos suaves |
| `--accent` | `#1c46bb` | CTA y acciones principales |

---

## 2.2 Escalas Extendidas

### Primary Scale

```css
50:  #eeeeff;
100: #d4d4fd;
200: #b0b0fb;
300: #7b7bf7;
400: #4d4de8;
500: #2d2dc0;
600: #050677;
700: #040561;
800: #03044b;
900: #020332;
```

### Accent Scale

```css
50:  #eef2ff;
100: #d8e1fd;
200: #b3c4fb;
300: #7d9ef7;
400: #4b78ef;
500: #1c46bb;
600: #1538a0;
700: #0f2b80;
800: #0a1f60;
900: #061240;
```

### Neutral Scale

```css
0:   #ffffff;
50:  #f8f8fc;
100: #f0f0f8;
200: #e2e2f0;
300: #c8c8de;
400: #a3a3be;
500: #7e7e9c;
600: #5e5e7a;
700: #42425a;
800: #2a2a40;
900: #14142a;
```

---

## 2.3 Colores Semánticos

### Success

| Token | HEX |
|---|---|
| Base | `#18a558` |
| Light | `#edfaf3` |
| Text | `#0d6e3a` |
| Border | `#2ab56a` |
| Mid | `#a3e6c3` |

### Warning

| Token | HEX |
|---|---|
| Base | `#f59e0b` |
| Light | `#fff8e6` |
| Text | `#7a4d04` |
| Border | `#f0a523` |
| Mid | `#fcd88a` |

### Error

| Token | HEX |
|---|---|
| Base | `#dc2626` |
| Light | `#fff0f0` |
| Text | `#8b1a1a` |
| Border | `#e05252` |
| Mid | `#f7b4b4` |

### Info

| Token | HEX |
|---|---|
| Base | `#2563eb` |
| Light | `#eef5ff` |
| Text | `#1a3d7a` |
| Border | `#5b8ee8` |
| Mid | `#a8c4f7` |

---

# 3. Tipografía

## Familias Tipográficas

### Display & Headings
- Open Sans
- Variable: `--font-display`

### Body & UI
- DM Sans
- Variable: `--font-body`

### Datos Numéricos
- DM Mono
- Variable: `--font-mono`

Uso exclusivo para:
- Signos vitales
- BPM
- SpO₂
- Timestamps
- Telemetría
