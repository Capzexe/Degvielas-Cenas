<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const fuelTypes = ['95', '98', 'Diesel', 'LPG'];

const fuelTypeLabels = {
    95: '95',
    98: '98',
    Diesel: 'Dīzelis',
    LPG: 'LPG',
};

const selectedFuelType = ref('95');
const stations = ref([]);
const isLoading = ref(false);
const errorMessage = ref('');
const mapElement = ref(null);
const map = ref(null);
const markerLayer = ref(null);

const selectedStations = computed(() => stations.value);

const stationGroupKey = (station) => {
    const price = selectedPrice(station);

    return [
        station.brand,
        station.selected_fuel_price,
        price?.source_type ?? '',
        price?.source_label ?? '',
        price?.source_url ?? '',
    ].join('|');
};

const groupedPriceRows = computed(() => {
    const groups = new Map();
    const rows = [];

    selectedStations.value.forEach((station) => {
        const canGroup = station.map_location_exact && station.selected_fuel_price !== null;
        const key = canGroup ? stationGroupKey(station) : `station-${station.id}`;

        if (!groups.has(key)) {
            const row = {
                ...station,
                id: key,
                station_ids: [station.id],
                station_count: 1,
                is_group: false,
                addresses: [{
                    id: station.id,
                    name: station.name,
                    address: station.address,
                    latitude: station.latitude,
                    longitude: station.longitude,
                }],
            };

            groups.set(key, row);
            rows.push(row);

            return;
        }

        const row = groups.get(key);

        row.station_ids.push(station.id);
        row.station_count += 1;
        row.is_group = true;
        row.name = `${station.brand} - ${row.station_count} stacijas`;
        row.address = `${row.station_count} adreses ar šo pašu publicēto cenu`;
        row.addresses.push({
            id: station.id,
            name: station.name,
            address: station.address,
            latitude: station.latitude,
            longitude: station.longitude,
        });
    });

    return rows;
});

const cheapestStation = computed(() => groupedPriceRows.value[0] ?? null);

const mapStations = computed(() => selectedStations.value.filter((station) => {
    return station.map_location_exact
        && Number.isFinite(Number(station.latitude))
        && Number.isFinite(Number(station.longitude));
}));

const cheapestMapStation = computed(() => mapStations.value[0] ?? null);

const averagePrice = computed(() => {
    const prices = groupedPriceRows.value
        .map((station) => station.selected_fuel_price)
        .filter((price) => price !== null);

    if (prices.length === 0) {
        return null;
    }

    return prices.reduce((sum, price) => sum + price, 0) / prices.length;
});

const formatPrice = (price) => {
    if (price === null || price === undefined) {
        return '-';
    }

    return `${Number(price).toFixed(3)} EUR`;
};

const fuelTypeLabel = (fuelType) => fuelTypeLabels[fuelType] ?? fuelType;

const getFuelPrice = (station, fuelType) => {
    return station.latest_prices.find((price) => price.fuel_type === fuelType) ?? null;
};

const freshnessLabel = (station) => {
    const price = getFuelPrice(station, selectedFuelType.value);

    if (!price?.fetched_at) {
        return 'Nav aktuālas cenas';
    }

    return new Intl.DateTimeFormat('lv-LV', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(price.fetched_at));
};

const brandTone = (brand) => {
    const tones = {
        'Circle K': 'border-red-400/40 bg-red-400/10 text-red-100',
        Neste: 'border-blue-400/40 bg-blue-400/10 text-blue-100',
        Virsi: 'border-emerald-400/40 bg-emerald-400/10 text-emerald-100',
        Viada: 'border-yellow-300/40 bg-yellow-300/10 text-yellow-100',
        'Straujupīte': 'border-orange-300/40 bg-orange-300/10 text-orange-100',
    };

    return tones[brand] ?? 'border-cyan-300/40 bg-cyan-300/10 text-cyan-100';
};

const selectedPrice = (station) => getFuelPrice(station, selectedFuelType.value);

const sourceLabel = (station) => {
    return selectedPrice(station)?.source_label ?? 'Avots nav zināms';
};

const sourceUrl = (station) => selectedPrice(station)?.source_url ?? null;

const sourceTone = (station) => {
    const sourceType = selectedPrice(station)?.source_type;

    if (sourceType?.startsWith('official')) {
        return 'border-emerald-300/30 bg-emerald-300/10 text-emerald-100';
    }

    if (sourceType === 'user_reported') {
        return 'border-cyan-300/30 bg-cyan-300/10 text-cyan-100';
    }

    return 'border-slate-400/30 bg-slate-400/10 text-slate-200';
};

const markerHtml = (station, isCheapest) => {
    const price = formatPrice(station.selected_fuel_price);
    const markerClass = isCheapest
        ? 'best-price-marker'
        : 'fuel-price-marker';

    return `
        <div class="${markerClass}">
            <span>${price}</span>
        </div>
    `;
};

const markerIcon = (station, isCheapest = false) => L.divIcon({
    className: 'fuel-map-marker-wrapper',
    html: markerHtml(station, isCheapest),
    iconSize: [1, 1],
    iconAnchor: [0, 0],
    popupAnchor: [0, -44],
});

const popupHtml = (station, isCheapest) => `
    <div class="fuel-map-popup">
        <p class="fuel-map-popup__rank">${isCheapest ? 'Lētākā cena' : station.brand}</p>
        <strong>${station.name}</strong>
        <span>${station.address}</span>
        <b>${formatPrice(station.selected_fuel_price)} / ${fuelTypeLabel(selectedFuelType.value)}</b>
        <small>${sourceLabel(station)}</small>
    </div>
`;

const initializeMap = async () => {
    await nextTick();

    if (!mapElement.value || map.value) {
        return;
    }

    map.value = L.map(mapElement.value, {
        zoomControl: false,
        scrollWheelZoom: false,
    }).setView([56.9496, 24.1052], 7);

    L.control.zoom({
        position: 'bottomright',
    }).addTo(map.value);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19,
    }).addTo(map.value);

    markerLayer.value = L.layerGroup().addTo(map.value);
};

const renderMapMarkers = async () => {
    await initializeMap();

    if (!map.value || !markerLayer.value) {
        return;
    }

    markerLayer.value.clearLayers();

    if (mapStations.value.length === 0) {
        map.value.setView([56.9496, 24.1052], 7);

        return;
    }

    const bounds = [];

    mapStations.value.forEach((station) => {
        const isCheapest = cheapestMapStation.value?.id === station.id;
        const position = [Number(station.latitude), Number(station.longitude)];

        bounds.push(position);

        L.marker(position, {
            icon: markerIcon(station, isCheapest),
            zIndexOffset: isCheapest ? 1000 : 0,
        })
            .bindPopup(popupHtml(station, isCheapest), {
                closeButton: false,
                className: 'fuel-map-popup-shell',
            })
            .addTo(markerLayer.value);
    });

    const cheapest = cheapestMapStation.value;

    if (cheapest) {
        map.value.setView([Number(cheapest.latitude), Number(cheapest.longitude)], mapStations.value.length === 1 ? 12 : 9, {
            animate: true,
        });
    } else if (bounds.length > 1) {
        map.value.fitBounds(bounds, {
            padding: [28, 28],
            maxZoom: 10,
        });
    }
};

const fetchStations = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/stations/cheapest?fuel_type=${encodeURIComponent(selectedFuelType.value)}`);

        if (!response.ok) {
            throw new Error('Neizdevās ielādēt degvielas cenas.');
        }

        const payload = await response.json();
        stations.value = payload.data;
        await renderMapMarkers();
    } catch (error) {
        errorMessage.value = error.message ?? 'Kaut kas nogāja greizi, ielādējot cenas.';
        stations.value = [];
        await renderMapMarkers();
    } finally {
        isLoading.value = false;
    }
};

watch(selectedFuelType, fetchStations);
watch(stations, renderMapMarkers);

onMounted(async () => {
    await initializeMap();
    await fetchStations();
});

onBeforeUnmount(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<template>
    <main class="min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(20,184,166,0.18),_transparent_32rem),#030712]">
        <section class="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-6 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-6 border-b border-white/10 pb-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-5xl">
                        Degvielas cenas Latvijā šodien
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-cyan-100/80 sm:text-base">
                        Salīdzini jaunākās zināmās publiskās degvielas cenas Latvijas lielākajos DUS tīklos. Katra cena rāda savu avotu, lai ir skaidrs, vai tā ir konkrētas stacijas cena vai publicēta tīkla cena.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex">
                    <div class="rounded-xl border border-emerald-300/30 bg-emerald-300/10 px-4 py-3">
                        <p class="text-xs uppercase text-emerald-100/70">Lētākā cena</p>
                        <p class="mt-1 text-xl font-semibold text-emerald-200">
                            {{ cheapestStation ? formatPrice(cheapestStation.selected_fuel_price) : '-' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-cyan-300/30 bg-cyan-300/10 px-4 py-3">
                        <p class="text-xs uppercase text-cyan-100/70">Vidēji</p>
                        <p class="mt-1 text-xl font-semibold text-cyan-100">
                            {{ formatPrice(averagePrice) }}
                        </p>
                    </div>
                </div>
            </header>

            <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-white/[0.03] p-4 shadow-2xl shadow-cyan-950/30 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Degvielas veids</h2>
                    <p class="mt-1 text-sm text-slate-300">Izvēlies degvielu, lai sakārtotu stacijas no lētākās cenas uz augstāko.</p>
                </div>

                <div class="grid grid-cols-4 gap-2 rounded-xl bg-gray-950/80 p-1">
                    <button
                        v-for="fuelType in fuelTypes"
                        :key="fuelType"
                        type="button"
                        class="rounded-lg px-3 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-emerald-300"
                        :class="selectedFuelType === fuelType ? 'bg-emerald-300 text-gray-950 shadow-lg shadow-emerald-500/20' : 'text-slate-300 hover:bg-white/[0.08] hover:text-white'"
                        @click="selectedFuelType = fuelType"
                    >
                        {{ fuelTypeLabel(fuelType) }}
                    </button>
                </div>
            </div>

            <section class="rounded-2xl border border-cyan-300/20 bg-cyan-950/20 p-4 shadow-2xl shadow-cyan-950/30">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Lētākā cena kartē</h2>
                        <p class="mt-1 max-w-2xl text-sm text-cyan-100/75">
                            Karte rāda tikai cenas, kurām ir zināma konkrēta DUS atrašanās vieta. Tīkla vai publicētās kopējās cenas paliek sarakstā zem kartes.
                        </p>
                    </div>

                    <div v-if="cheapestMapStation" class="rounded-xl border border-emerald-300/25 bg-emerald-300/10 px-4 py-3 md:min-w-72">
                        <p class="text-xs uppercase text-emerald-100/70">Labākā cena kartē</p>
                        <p class="mt-1 truncate text-base font-semibold text-white">{{ cheapestMapStation.name }}</p>
                        <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-200">
                            {{ formatPrice(cheapestMapStation.selected_fuel_price) }}
                        </p>
                    </div>
                </div>

                <div class="relative mt-4 overflow-hidden rounded-xl border border-cyan-300/20 bg-gray-950">
                    <div ref="mapElement" class="h-[34rem] w-full"></div>

                    <div
                        v-if="!isLoading && mapStations.length === 0"
                        class="absolute inset-4 z-[500] flex items-center justify-center rounded-xl border border-dashed border-cyan-300/30 bg-gray-950/85 p-6 text-center backdrop-blur"
                    >
                        <p class="text-sm leading-6 text-slate-200">
                            Šim degvielas veidam pagaidām nav cenu ar konkrētu pārbaudāmu DUS lokāciju. Cenas joprojām redzamas sarakstā zem kartes.
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid gap-6">
                <section class="min-h-[32rem] rounded-2xl border border-white/10 bg-gray-950/[0.72] p-3 shadow-2xl shadow-black/30">
                    <div class="flex items-center justify-between px-2 pb-3">
                        <h2 class="text-lg font-semibold text-white">Lētākie cenu ieraksti</h2>
                        <span class="rounded-full border border-cyan-300/30 px-3 py-1 text-xs font-medium text-cyan-100">
                            {{ groupedPriceRows.length }} rezultāti
                        </span>
                    </div>

                    <div v-if="errorMessage" class="rounded-xl border border-red-400/40 bg-red-400/10 p-4 text-sm text-red-100">
                        {{ errorMessage }}
                    </div>

                    <div v-else-if="isLoading" class="grid gap-3">
                        <div v-for="item in 5" :key="item" class="h-24 animate-pulse rounded-xl bg-white/[0.07]"></div>
                    </div>

                    <div v-else-if="groupedPriceRows.length === 0" class="rounded-xl border border-dashed border-cyan-300/30 p-8 text-center text-slate-300">
                        Pagaidām nav cenu degvielai {{ fuelTypeLabel(selectedFuelType) }}. Palaid <span class="font-mono text-emerald-200">php artisan fetch:fuel-prices</span>.
                    </div>

                    <ol v-else class="grid gap-3">
                        <li
                            v-for="(station, index) in groupedPriceRows"
                            :key="station.id"
                            class="group rounded-xl border border-white/10 bg-white/[0.04] p-4 transition hover:border-emerald-300/50 hover:bg-white/[0.07]"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="flex size-8 items-center justify-center rounded-lg bg-gray-900 text-sm font-semibold text-emerald-200">
                                            {{ index + 1 }}
                                        </span>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold" :class="brandTone(station.brand)">
                                            {{ station.brand }}
                                        </span>
                                        <span class="text-xs text-slate-400">{{ freshnessLabel(station) }}</span>
                                    </div>
                                    <h3 class="mt-3 truncate text-lg font-semibold text-white">{{ station.name }}</h3>
                                    <p class="mt-1 text-sm text-slate-300">{{ station.address }}</p>
                                    <div
                                        v-if="station.is_group"
                                        class="mt-3 rounded-lg border border-white/10 bg-gray-950/60 p-3"
                                    >
                                        <p class="text-xs font-semibold uppercase text-cyan-100/70">
                                            Adreses šai cenai
                                        </p>
                                        <ul class="mt-2 grid gap-1.5 text-sm leading-5 text-slate-300 sm:grid-cols-2">
                                            <li
                                                v-for="address in station.addresses"
                                                :key="address.id"
                                            >
                                                <span class="font-semibold text-slate-100">{{ address.name }}</span>
                                                <span class="block text-xs text-slate-400">{{ address.address }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <a
                                            v-if="sourceUrl(station)"
                                            :href="sourceUrl(station)"
                                            target="_blank"
                                            rel="noreferrer"
                                            class="rounded-full border px-2.5 py-1 text-xs font-semibold transition hover:border-white/50"
                                            :class="sourceTone(station)"
                                        >
                                            {{ sourceLabel(station) }}
                                        </a>
                                        <span
                                            v-else
                                            class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            :class="sourceTone(station)"
                                        >
                                            {{ sourceLabel(station) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-3xl font-semibold tabular-nums text-emerald-200">
                                        {{ formatPrice(station.selected_fuel_price) }}
                                    </p>
                                    <p class="mt-1 text-xs uppercase text-slate-400">{{ fuelTypeLabel(selectedFuelType) }} / litrā</p>
                                </div>
                            </div>
                        </li>
                    </ol>
                </section>
            </div>
        </section>
    </main>
</template>

<style>
.leaflet-container {
    background: #020617;
    color: #e0f2fe;
    font-family: inherit;
}

.leaflet-control-attribution {
    background: rgba(2, 6, 23, 0.82) !important;
    color: rgba(207, 250, 254, 0.72) !important;
}

.leaflet-control-attribution a {
    color: #67e8f9 !important;
}

.leaflet-control-zoom a {
    background: rgba(15, 23, 42, 0.94) !important;
    border-color: rgba(103, 232, 249, 0.22) !important;
    color: #ccfbf1 !important;
}

.fuel-map-marker-wrapper {
    background: transparent;
    border: 0;
    height: 1px !important;
    margin: 0 !important;
    width: 1px !important;
}

.fuel-price-marker,
.best-price-marker {
    align-items: center;
    border-radius: 999px;
    display: flex;
    font-size: 12px;
    font-weight: 800;
    height: 34px;
    justify-content: center;
    line-height: 1;
    left: 0;
    position: absolute;
    top: 0;
    transform: translate(-50%, calc(-100% - 8px));
    white-space: nowrap;
}

.fuel-price-marker {
    background: rgba(8, 47, 73, 0.94);
    border: 1px solid rgba(103, 232, 249, 0.62);
    box-shadow: 0 14px 30px rgba(8, 47, 73, 0.45);
    color: #cffafe;
    width: 88px;
}

.best-price-marker {
    background: #6ee7b7;
    border: 1px solid rgba(236, 253, 245, 0.95);
    box-shadow: 0 18px 38px rgba(16, 185, 129, 0.42);
    color: #03140f;
    width: 104px;
}

.fuel-price-marker::after,
.best-price-marker::after {
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    bottom: -7px;
    content: '';
    left: 50%;
    position: absolute;
    transform: translateX(-50%);
}

.fuel-price-marker::after {
    border-top: 8px solid rgba(8, 47, 73, 0.94);
}

.best-price-marker::after {
    border-top: 8px solid #6ee7b7;
}

.fuel-map-popup-shell .leaflet-popup-content-wrapper {
    background: #020617;
    border: 1px solid rgba(103, 232, 249, 0.22);
    border-radius: 14px;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45);
    color: #e2e8f0;
}

.fuel-map-popup-shell .leaflet-popup-tip {
    background: #020617;
}

.fuel-map-popup {
    display: grid;
    gap: 5px;
    min-width: 180px;
}

.fuel-map-popup__rank {
    color: #6ee7b7;
    font-size: 11px;
    font-weight: 800;
    margin: 0;
    text-transform: uppercase;
}

.fuel-map-popup strong,
.fuel-map-popup span,
.fuel-map-popup b,
.fuel-map-popup small {
    display: block;
}

.fuel-map-popup strong {
    color: #ffffff;
    font-size: 14px;
}

.fuel-map-popup span,
.fuel-map-popup small {
    color: #bae6fd;
    font-size: 12px;
}

.fuel-map-popup b {
    color: #a7f3d0;
    font-size: 15px;
}
</style>
