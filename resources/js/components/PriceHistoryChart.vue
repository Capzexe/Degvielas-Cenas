<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    fuelType: {
        type: String,
        required: true,
    },
});

const fuelTypeLabels = {
    95: '95',
    98: '98',
    Diesel: 'Dīzelis',
    LPG: 'LPG',
};

const days = ref(90);
const series = ref([]);
const isLoading = ref(false);
const errorMessage = ref('');

const chartWidth = 720;
const chartHeight = 260;
const padding = {
    top: 20,
    right: 28,
    bottom: 34,
    left: 56,
};

const colors = ['#6ee7b7', '#22d3ee', '#facc15', '#f87171', '#a78bfa', '#fb923c'];

const allPoints = computed(() => series.value.flatMap((item) => item.points));

const hasHistory = computed(() => allPoints.value.length > 1);

const priceBounds = computed(() => {
    const prices = allPoints.value.map((point) => point.price);

    if (prices.length === 0) {
        return { min: 0, max: 1 };
    }

    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const paddingValue = Math.max((max - min) * 0.18, 0.02);

    return {
        min: Math.max(0, min - paddingValue),
        max: max + paddingValue,
    };
});

const timeBounds = computed(() => {
    const times = allPoints.value.map((point) => new Date(point.date).getTime());

    if (times.length === 0) {
        const now = Date.now();

        return { min: now - 1, max: now };
    }

    const min = Math.min(...times);
    const max = Math.max(...times);

    return {
        min,
        max: min === max ? max + 1 : max,
    };
});

const yTicks = computed(() => {
    const ticks = [];
    const step = (priceBounds.value.max - priceBounds.value.min) / 4;

    for (let index = 0; index <= 4; index += 1) {
        ticks.push(priceBounds.value.min + step * index);
    }

    return ticks.reverse();
});

const xPosition = (date) => {
    const time = new Date(date).getTime();
    const usableWidth = chartWidth - padding.left - padding.right;

    return padding.left + ((time - timeBounds.value.min) / (timeBounds.value.max - timeBounds.value.min)) * usableWidth;
};

const yPosition = (price) => {
    const usableHeight = chartHeight - padding.top - padding.bottom;

    return padding.top + ((priceBounds.value.max - price) / (priceBounds.value.max - priceBounds.value.min)) * usableHeight;
};

const polylinePoints = (points) => {
    return points
        .map((point) => `${xPosition(point.date).toFixed(1)},${yPosition(point.price).toFixed(1)}`)
        .join(' ');
};

const formatPrice = (price) => `${Number(price).toFixed(3)} EUR`;

const formatDate = (date) => new Intl.DateTimeFormat('lv-LV', {
    day: '2-digit',
    month: 'short',
}).format(new Date(date));

const fuelTypeLabel = computed(() => fuelTypeLabels[props.fuelType] ?? props.fuelType);

const latestDate = computed(() => {
    if (allPoints.value.length === 0) {
        return 'Nav datu';
    }

    const latest = allPoints.value.reduce((currentLatest, point) => {
        return new Date(point.date) > new Date(currentLatest.date) ? point : currentLatest;
    });

    return formatDate(latest.date);
});

const fetchHistory = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/prices/history?fuel_type=${encodeURIComponent(props.fuelType)}&days=${days.value}`);

        if (!response.ok) {
            throw new Error('Neizdevās ielādēt cenu vēsturi.');
        }

        const payload = await response.json();
        series.value = payload.series;
    } catch (error) {
        errorMessage.value = error.message ?? 'Kļūda, ielādējot cenu vēsturi.';
        series.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.fuelType, fetchHistory);
watch(days, fetchHistory);
onMounted(fetchHistory);
</script>

<template>
    <section class="rounded-2xl border border-white/10 bg-white/[0.035] p-4 shadow-2xl shadow-cyan-950/20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Cenu vēsture</h2>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-300">
                    Diagramma rāda tikai mūsu aplikācijā savāktos ierakstus. Jo ilgāk darbojas cenu ielāde, jo pilnāka kļūst vēsture.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in [30, 90, 180, 365]"
                    :key="option"
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    :class="days === option ? 'bg-cyan-300 text-gray-950' : 'bg-gray-950 text-slate-300 hover:bg-white/[0.08] hover:text-white'"
                    @click="days = option"
                >
                    {{ option }} d.
                </button>
            </div>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="overflow-hidden rounded-xl border border-cyan-300/20 bg-gray-950">
                <div class="flex items-center justify-between border-b border-white/10 px-4 py-3">
                    <span class="text-sm font-semibold text-cyan-100">{{ fuelTypeLabel }} cenu kustība</span>
                    <span class="text-xs text-slate-400">Pēdējais ieraksts: {{ latestDate }}</span>
                </div>

                <div v-if="errorMessage" class="p-6 text-sm text-red-100">
                    {{ errorMessage }}
                </div>

                <div v-else-if="isLoading" class="h-80 animate-pulse bg-white/[0.04]"></div>

                <div v-else-if="!hasHistory" class="flex h-80 items-center justify-center px-6 text-center text-sm leading-6 text-slate-300">
                    Vēl nav pietiekami daudz vēstures datu diagrammai. Palaid cenu ielādi vairākas reizes dažādos laikos.
                </div>

                <svg
                    v-else
                    class="h-80 w-full"
                    :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                    role="img"
                    :aria-label="`${fuelTypeLabel} cenu vēstures diagramma`"
                    preserveAspectRatio="none"
                >
                    <g>
                        <line
                            v-for="tick in yTicks"
                            :key="tick"
                            :x1="padding.left"
                            :x2="chartWidth - padding.right"
                            :y1="yPosition(tick)"
                            :y2="yPosition(tick)"
                            stroke="rgba(148, 163, 184, 0.16)"
                            stroke-width="1"
                        />
                        <text
                            v-for="tick in yTicks"
                            :key="`label-${tick}`"
                            :x="padding.left - 10"
                            :y="yPosition(tick) + 4"
                            text-anchor="end"
                            fill="#94a3b8"
                            font-size="12"
                        >
                            {{ tick.toFixed(2) }}
                        </text>
                    </g>

                    <polyline
                        v-for="(item, index) in series"
                        :key="item.name"
                        :points="polylinePoints(item.points)"
                        fill="none"
                        :stroke="colors[index % colors.length]"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <g v-for="(item, index) in series" :key="`${item.name}-points`">
                        <circle
                            v-for="point in item.points"
                            :key="`${item.name}-${point.date}`"
                            :cx="xPosition(point.date)"
                            :cy="yPosition(point.price)"
                            r="3.5"
                            :fill="colors[index % colors.length]"
                        >
                            <title>{{ item.name }} - {{ formatDate(point.date) }} - {{ formatPrice(point.price) }}</title>
                        </circle>
                    </g>
                </svg>
            </div>

            <aside class="rounded-xl border border-white/10 bg-gray-950/70 p-4">
                <h3 class="text-sm font-semibold text-white">Līnijas</h3>
                <div v-if="series.length === 0" class="mt-3 text-sm text-slate-400">
                    Nav ko attēlot.
                </div>
                <div v-else class="mt-3 grid gap-3">
                    <div v-for="(item, index) in series" :key="item.name" class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: colors[index % colors.length] }"></span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-100">{{ item.name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ item.source_label }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</template>
