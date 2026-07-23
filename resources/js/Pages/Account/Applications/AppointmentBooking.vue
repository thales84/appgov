<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, CheckCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  locations: {
    type: Array,
    required: true,
  },
});

const selectedLocation = ref(props.locations[0] || null);
const selectedSlot = ref(null);

const form = useForm({
  slot_public_id: '',
});

const submitBooking = () => {
  if (!selectedSlot.value) return;
  form.slot_public_id = selectedSlot.value.publicId;
  form.post(route('account.applications.appointment.store', props.application.publicId));
};
</script>

<template>
  <Head :title="`Réservation de Rendez-vous — ${application.reference}`" />

  <AppShell :title="`Réservation de Rendez-vous`" eyebrow="Espace Usager — Planification d'épreuve">
    <div class="max-w-4xl mx-auto space-y-6">
      <Link
        :href="route('account.applications.show', application.publicId)"
        class="text-xs font-semibold text-slate-500 hover:text-slate-700 flex items-center gap-1"
      >
        <ArrowLeftIcon class="w-4 h-4" /> Retour au dossier {{ application.reference }}
      </Link>

      <!-- Active Appointment Card -->
      <div v-if="application.appointment" class="bg-emerald-50 border border-emerald-200 rounded-lg p-5 flex items-center gap-4">
        <CheckCircleIcon class="w-8 h-8 text-emerald-600 shrink-0" />
        <div>
          <h3 class="text-sm font-bold text-emerald-900">Rendez-vous confirmé</h3>
          <p class="text-xs text-emerald-800 mt-1">
            <strong>Date & Heure :</strong> {{ new Date(application.appointment.scheduledAt).toLocaleString('fr-FR') }}
          </p>
          <p class="text-xs text-emerald-700">
            <strong>Centre :</strong> {{ application.appointment.locationName.fr }} — {{ application.appointment.address }}
          </p>
        </div>
      </div>

      <!-- Booking Form -->
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm space-y-6">
        <div>
          <h2 class="font-display text-lg font-bold text-brand-blue-900">Choix du centre et du créneau</h2>
          <p class="text-xs text-slate-500">Sélectionnez le centre agréé et la plage horaire souhaitée pour passer votre épreuve.</p>
        </div>

        <div v-if="locations.length === 0" class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
          Aucun centre de rendez-vous n'est actuellement ouvert pour ce service.
        </div>

        <div v-else class="space-y-6">
          <!-- Location selector -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">Centre d'examen</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <button
                v-for="loc in locations"
                :key="loc.publicId"
                type="button"
                class="border text-left p-3.5 rounded-lg transition"
                :class="selectedLocation?.publicId === loc.publicId ? 'border-brand-blue-600 bg-brand-blue-50/50' : 'border-slate-200 hover:border-slate-300'"
                @click="selectedLocation = loc; selectedSlot = null"
              >
                <div class="flex items-center gap-2 text-xs font-bold text-slate-900">
                  <MapPinIcon class="w-4 h-4 text-brand-blue-600 shrink-0" />
                  {{ loc.name.fr }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1 pl-6">{{ loc.address }}</p>
              </button>
            </div>
          </div>

          <!-- Slots selector -->
          <div v-if="selectedLocation">
            <label class="block text-xs font-semibold text-slate-700 mb-2">Créneaux horaires disponibles</label>

            <div v-if="selectedLocation.slots.length === 0" class="text-xs text-slate-500 italic p-3 bg-slate-50 rounded">
              Aucun créneau disponible pour ce centre.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <button
                v-for="slot in selectedLocation.slots"
                :key="slot.publicId"
                type="button"
                :disabled="slot.isFull"
                class="border p-3 rounded-lg text-left transition"
                :class="[
                  selectedSlot?.publicId === slot.publicId ? 'border-brand-blue-600 bg-brand-blue-50/50' : 'border-slate-200 hover:border-slate-300',
                  slot.isFull ? 'opacity-50 cursor-not-allowed bg-slate-100' : 'cursor-pointer'
                ]"
                @click="selectedSlot = slot"
              >
                <div class="flex items-center gap-2 text-xs font-bold text-slate-900">
                  <CalendarIcon class="w-4 h-4 text-slate-500" />
                  {{ new Date(slot.startsAt).toLocaleDateString('fr-FR') }}
                </div>
                <p class="text-[11px] font-mono text-slate-600 mt-1 pl-6">
                  {{ new Date(slot.startsAt).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}
                </p>
                <p class="text-[10px] text-slate-400 mt-1 pl-6">
                  {{ slot.isFull ? 'Complet' : `Places : ${slot.bookedCount}/${slot.maxCapacity}` }}
                </p>
              </button>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t">
            <button
              type="button"
              :disabled="!selectedSlot || form.processing"
              class="px-6 py-2.5 bg-brand-blue-600 hover:bg-brand-blue-900 disabled:opacity-50 text-white text-xs font-bold rounded shadow transition"
              @click="submitBooking"
            >
              Confirmer la réservation
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
