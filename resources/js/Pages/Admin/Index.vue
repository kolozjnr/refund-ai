<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({ summary: Object, requests: Object, filter: String });

const colors = {
  PENDING: 'bg-sky-100 text-sky-800',
  APPROVED: 'bg-emerald-100 text-emerald-800',
  DENIED: 'bg-rose-100 text-rose-800',
  ESCALATED: 'bg-amber-100 text-amber-900',
};

function setFilter(value) {
  router.get('/admin/refunds', value ? { decision: value } : {}, { preserveState: true });
}
</script>

<template>
  <AppLayout>
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
      <div>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-amber-700">Support workspace</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Refund requests</h1>
        <p class="mt-2 text-sm text-stone-500">Review decisions and investigate escalated cases.</p>
      </div>
      <select :value="filter || ''" @change="setFilter($event.target.value)" class="rounded-lg border-stone-300 bg-white text-sm">
        <option value="">All decisions</option>
        <option>PENDING</option>
        <option>APPROVED</option>
        <option>DENIED</option>
        <option>ESCALATED</option>
      </select>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div v-for="(value, key) in { total: summary.total, approved: summary.approved, denied: summary.denied, escalated: summary.escalated }" :key="key" class="rounded-xl border border-stone-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">{{ key === 'total' ? 'Total requests' : key }}</p>
        <p class="mt-2 text-3xl font-semibold">{{ value }}</p>
      </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-xl border border-stone-200 bg-white">
      <div class="border-b border-stone-100 px-5 py-4"><h2 class="font-semibold">Recent requests</h2></div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-400">
            <tr>
              <th class="px-5 py-3">Request</th>
              <th class="px-5 py-3">Customer</th>
              <th class="px-5 py-3">Order</th>
              <th class="px-5 py-3">Amount</th>
              <th class="px-5 py-3">Decision</th>
              <th class="px-5 py-3">Submitted</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-100">
            <tr v-for="r in requests.data" :key="r.id" class="hover:bg-stone-50">
              <td class="px-5 py-4"><a :href="`/admin/refunds/${r.id}`" class="font-semibold hover:underline">#{{ r.id }}</a></td>
              <td class="px-5 py-4">{{ r.customer }}</td>
              <td class="px-5 py-4">{{ r.order }}</td>
              <td class="px-5 py-4">{{ r.amount ? `${r.currency} ${Number(r.amount).toFixed(2)}` : '—' }}</td>
              <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[11px] font-bold" :class="colors[r.decision]">{{ r.decision }}</span></td>
              <td class="whitespace-nowrap px-5 py-4 text-stone-500">{{ r.created_at }}</td>
              <td class="px-5 py-4 text-right">
                <a :href="`/admin/refunds/${r.id}`" :aria-label="`View refund request ${r.id}`" :title="`View request #${r.id}`" class="inline-flex rounded-md p-2 text-stone-500 transition hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                    <circle cx="12" cy="12" r="2.75" />
                  </svg>
                </a>
              </td>
            </tr>
            <tr v-if="!requests.data.length"><td colspan="7" class="px-5 py-10 text-center text-stone-500">No refund requests yet.</td></tr>
          </tbody>
        </table>
      </div>
      <div class="flex justify-between border-t border-stone-100 px-5 py-3 text-sm">
        <a v-if="requests.prev_page_url" :href="requests.prev_page_url" class="text-stone-600">← Previous</a><span v-else></span>
        <span class="text-stone-400">Page {{ requests.current_page }} of {{ requests.last_page }}</span>
        <a v-if="requests.next_page_url" :href="requests.next_page_url" class="text-stone-600">Next →</a><span v-else></span>
      </div>
    </div>
  </AppLayout>
</template>
