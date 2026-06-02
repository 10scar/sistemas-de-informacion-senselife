const CHART_WIDTH = 400;
const CHART_HEIGHT = 120;

const UMBRAL_COLORS = {
    critico_alto: 'var(--color-error)',
    critico_bajo: 'var(--color-error)',
    alerta_alto: 'var(--color-warning)',
    alerta_bajo: 'var(--color-warning)',
};

function valorAY(valor, min, span, height) {
    return height - ((valor - min) / span) * height;
}

function rango(valores, minFallback, maxFallback) {
    if (valores.length === 0) {
        return { min: minFallback, max: maxFallback };
    }

    let min = Math.min(...valores);
    let max = Math.max(...valores);

    if (min === max) {
        const pad = Math.max(min * 0.05, 5);
        min -= pad;
        max += pad;
    } else {
        const pad = (max - min) * 0.12;
        min -= pad;
        max += pad;
    }

    return { min, max };
}

function rangoConUmbrales(valores, minFallback, maxFallback, umbrales) {
    const base = rango(valores, minFallback, maxFallback);
    for (const key of ['alerta_alto', 'alerta_bajo', 'critico_alto', 'critico_bajo']) {
        const umbral = umbrales[key];
        base.min = Math.min(base.min, umbral - 8);
        base.max = Math.max(base.max, umbral + 8);
    }
    return base;
}

function svgPath(valores, rango) {
    if (valores.length === 0) {
        const y = CHART_HEIGHT / 2;
        return `M0,${y} L${CHART_WIDTH},${y}`;
    }

    const span = Math.max(rango.max - rango.min, 1);
    const n = valores.length;

    if (n === 1) {
        const y = Math.round(valorAY(valores[0], rango.min, span, CHART_HEIGHT));
        return `M0,${y} L${CHART_WIDTH},${y}`;
    }

    return valores
        .map((valor, i) => {
            const x = Math.round((i / (n - 1)) * CHART_WIDTH * 10) / 10;
            const y = Math.round(valorAY(valor, rango.min, span, CHART_HEIGHT) * 10) / 10;
            return `${i === 0 ? 'M' : 'L'}${x},${y}`;
        })
        .join(' ');
}

function marcasEjeY(rango, cantidad = 3) {
    if (cantidad <= 1) {
        return [Math.round((rango.min + rango.max) / 2)];
    }

    const marcas = [];
    for (let i = 0; i < cantidad; i++) {
        marcas.push(Math.round(rango.min + ((rango.max - rango.min) * i) / (cantidad - 1)));
    }

    return marcas.reverse();
}

function formatHora(date) {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
}

function marcasEjeTiempo(tiempos, maxMarcas = 4) {
    const n = tiempos.length;
    if (n === 0) {
        return [];
    }

    let indices;
    if (n <= maxMarcas) {
        indices = Array.from({ length: n }, (_, i) => i);
    } else {
        indices = [];
        for (let i = 0; i < maxMarcas; i++) {
            indices.push(Math.floor((i * (n - 1)) / Math.max(maxMarcas - 1, 1)));
        }
    }

    return indices.map((idx) => ({
        label: formatHora(tiempos[idx]),
        pct: n > 1 ? (idx / (n - 1)) * 100 : 0,
    }));
}

function lineasUmbral(umbrales, rango) {
    const span = Math.max(rango.max - rango.min, 1);
    const niveles = [
        ['critico_alto', umbrales.critico_alto],
        ['critico_bajo', umbrales.critico_bajo],
        ['alerta_alto', umbrales.alerta_alto],
        ['alerta_bajo', umbrales.alerta_bajo],
    ];

    return niveles.map(([nivel, valor]) => ({
        nivel,
        y: Math.round(valorAY(valor, rango.min, span, CHART_HEIGHT) * 10) / 10,
    }));
}

function calcularTendencia(actual, valores, tiempos) {
    const desde = Date.now() - 60 * 60 * 1000;
    const valsHora = [];

    for (let i = 0; i < valores.length; i++) {
        if (tiempos[i].getTime() >= desde) {
            valsHora.push(valores[i]);
        }
    }

    if (valsHora.length === 0) {
        return null;
    }

    const media = valsHora.reduce((a, b) => a + b, 0) / valsHora.length;
    if (media === 0) {
        return null;
    }

    return Math.round(((actual - media) / media) * 100);
}

function nextValor(last, min, max) {
    const delta = (Math.random() - 0.45) * 4;
    return Math.min(max, Math.max(min, Math.round(last + delta)));
}

function tick(state) {
    const stepMs = state.intervalMs ?? 2500;

    const lastTime = state.tiempos[state.tiempos.length - 1].getTime();
    state.tiempos.shift();
    state.tiempos.push(new Date(lastTime + stepMs));

    state.valores.shift();
    const last = state.valores[state.valores.length - 1];
    state.valores.push(nextValor(last, state.valorMin, state.valorMax));
}

function renderChart(root, state) {
    const { valores, tiempos, umbrales, minFallback, maxFallback } = state;
    const rangoActual = rangoConUmbrales(valores, minFallback, maxFallback, umbrales);
    const span = Math.max(rangoActual.max - rangoActual.min, 1);
    const actual = valores[valores.length - 1];
    const promedio = Math.round(valores.reduce((a, b) => a + b, 0) / valores.length);
    const tendencia = calcularTendencia(actual, valores, tiempos);

    const path = root.querySelector('[data-landing-path]');
    if (path) {
        path.setAttribute('d', svgPath(valores, rangoActual));
    }

    const valorEl = root.querySelector('[data-landing-valor]');
    if (valorEl) {
        valorEl.textContent = String(Math.round(actual));
    }

    const promedioEl = root.querySelector('[data-landing-promedio]');
    if (promedioEl) {
        promedioEl.textContent = String(promedio);
    }

    const tendenciaEl = root.querySelector('[data-landing-tendencia]');
    if (tendenciaEl) {
        if (tendencia === null) {
            tendenciaEl.classList.add('hidden');
        } else {
            tendenciaEl.classList.remove('hidden');
            const sube = tendencia >= 0;
            const abs = Math.abs(tendencia);
            const sign = sube ? '+' : '−';
            tendenciaEl.classList.toggle('text-error', sube);
            tendenciaEl.classList.toggle('text-success', !sube);
            tendenciaEl.querySelector('[data-landing-tendencia-text]').textContent =
                `${sign}${abs}% vs media h.`;
        }
    }

    const yAxis = root.querySelector('[data-landing-y-axis]');
    if (yAxis) {
        yAxis.innerHTML = '';
        for (const tickValue of marcasEjeY(rangoActual)) {
            const topPct = ((rangoActual.max - tickValue) / span) * 100;
            const spanEl = document.createElement('span');
            spanEl.className =
                'absolute left-0 -translate-y-1/2 text-[10px] font-semibold tabular-nums text-neutral-400';
            spanEl.style.top = `${Math.round(topPct * 10) / 10}%`;
            spanEl.textContent = String(tickValue);
            yAxis.appendChild(spanEl);
        }
    }

    const grid = root.querySelector('[data-landing-grid]');
    if (grid) {
        grid.innerHTML = '';
        for (const tickValue of marcasEjeY(rangoActual)) {
            const topPct = ((rangoActual.max - tickValue) / span) * 100;
            const line = document.createElement('span');
            line.className =
                'pointer-events-none absolute left-0 right-0 border-t border-dashed border-neutral-200';
            line.style.top = `${Math.round(topPct * 10) / 10}%`;
            line.setAttribute('aria-hidden', 'true');
            grid.appendChild(line);
        }
    }

    const umbralesGroup = root.querySelector('[data-landing-umbrales]');
    if (umbralesGroup) {
        umbralesGroup.innerHTML = '';
        for (const linea of lineasUmbral(umbrales, rangoActual)) {
            const esCritico = linea.nivel.startsWith('critico');
            const el = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            el.setAttribute('x1', '0');
            el.setAttribute('y1', String(linea.y));
            el.setAttribute('x2', String(CHART_WIDTH));
            el.setAttribute('y2', String(linea.y));
            el.setAttribute('stroke', UMBRAL_COLORS[linea.nivel] ?? 'var(--color-neutral-400)');
            el.setAttribute('stroke-width', esCritico ? '1.75' : '1.25');
            el.setAttribute('stroke-dasharray', esCritico ? '6 4' : '4 3');
            el.setAttribute('opacity', esCritico ? '1' : '0.85');
            umbralesGroup.appendChild(el);
        }
    }

    const xAxis = root.querySelector('[data-landing-x-axis]');
    if (xAxis) {
        xAxis.innerHTML = '';
        for (const marca of marcasEjeTiempo(tiempos)) {
            const spanEl = document.createElement('span');
            spanEl.className =
                'absolute -translate-x-1/2 whitespace-nowrap text-[10px] font-medium tabular-nums text-neutral-400';
            spanEl.style.left = `${marca.pct}%`;
            spanEl.textContent = marca.label;
            xAxis.appendChild(spanEl);
        }
    }
}

function initLandingMonitor(root) {
    const raw = root.getAttribute('data-landing-monitor');
    if (!raw) {
        return;
    }

    const config = JSON.parse(raw);
    const state = {
        valores: config.valores.map(Number),
        tiempos: config.tiempos.map((t) => new Date(t)),
        umbrales: config.umbrales,
        minFallback: config.minFallback,
        maxFallback: config.maxFallback,
        valorMin: config.valorMin,
        valorMax: config.valorMax,
        intervalMs: config.intervalMs ?? 2500,
    };

    renderChart(root, state);

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!prefersReduced) {
        window.setInterval(() => {
            tick(state);
            renderChart(root, state);
        }, state.intervalMs);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-landing-monitor]').forEach(initLandingMonitor);
});
