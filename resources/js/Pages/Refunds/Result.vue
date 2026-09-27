<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
defineProps({ refund: Object, customer: Object, order: Object, items: Array });
const colors = { PENDING: 'bg-sky-100 text-sky-800', APPROVED: 'bg-emerald-100 text-emerald-800', DENIED: 'bg-rose-100 text-rose-800', ESCALATED: 'bg-amber-100 text-amber-900' };
</script>
<template>
  <AppLayout>
    <div class="mx-auto max-w-2xl">
      <a href="/refunds" class="text-sm font-medium text-stone-500 hover:text-stone-900">← Back to refunds</a>
      <section class="mt-6 rounded-2xl border border-stone-200 bg-white p-7 shadow-sm sm:p-10">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-stone-400">Request #{{ refund.id }}</p>
        <span class="mt-5 inline-flex rounded-full px-3 py-1 text-xs font-bold tracking-wide" :class="colors[refund.status === 'completed' ? refund.final_decision : 'PENDING']">{{ refund.status === 'completed' ? refund.final_decision : 'PROCESSING' }}</span>
        <h1 class="mt-5 text-3xl font-semibold">{{ refund.status !== 'completed' ? 'Your request is being processed.' : refund.final_decision === 'APPROVED' ? 'Your refund is approved.' : refund.final_decision === 'DENIED' ? 'This order isn’t eligible.' : 'A specialist will take a closer look.' }}</h1>
        <p class="mt-3 leading-7 text-stone-600">{{ refund.decision_reason }}</p>
        <div v-if="refund.status !== 'completed'" class="mt-5 rounded-lg bg-sky-50 p-4 text-sm text-sky-950">Your request is in the processing queue. Refresh this page in a moment to see the result.</div>
        <div v-else-if="refund.final_decision === 'ESCALATED'" class="mt-5 rounded-lg bg-amber-50 p-4 text-sm text-amber-950">Your request is in the support queue. A specialist will review it and follow up.</div>
        <div class="mt-8 grid gap-5 border-t border-stone-100 pt-6 sm:grid-cols-2">
          <div><p class="text-xs uppercase tracking-wide text-stone-400">Customer</p><p class="mt-1 font-medium">{{ customer.name }}</p></div>
          <div><p class="text-xs uppercase tracking-wide text-stone-400">Order</p><p class="mt-1 font-medium">{{ order.order_number }}</p></div>
          <div><p class="text-xs uppercase tracking-wide text-stone-400">Requested amount</p><p class="mt-1 font-medium">{{ refund.requested_amount ? `${order.currency} ${Number(refund.requested_amount).toFixed(2)}` : 'Full order amount' }}</p></div>
          <div><p class="text-xs uppercase tracking-wide text-stone-400">Submitted</p><p class="mt-1 font-medium">{{ new Date(refund.created_at).toLocaleString() }}</p></div>
        </div>
        <blockquote class="mt-7 border-l-2 border-amber-500 pl-4 text-sm leading-6 text-stone-600">“{{ refund.message }}”</blockquote>
        <a href="/refunds" class="mt-8 inline-block rounded-lg bg-stone-900 px-5 py-3 text-sm font-semibold text-white hover:bg-stone-700">Submit another request</a>
      </section>
    </div>
  </AppLayout>
</template>
