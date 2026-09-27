<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({ customers: Array, flash: Object });
const form = useForm({ customer_id: '', order_id: '', message: '', requested_amount: '' });
const customer = computed(() => props.customers.find(c => String(c.id) === String(form.customer_id)));
const orders = computed(() => customer.value?.orders ?? []);
const order = computed(() => orders.value.find(o => String(o.id) === String(form.order_id)));
function changeCustomer() { form.order_id = ''; }
function submit() { form.post('/refunds'); }
</script>
<template>
<AppLayout>
  <div class="grid gap-10 lg:grid-cols-[1fr_390px]">
    <section class="pt-3"><p class="text-xs font-bold uppercase tracking-[.2em] text-amber-700">Customer care</p><h1 class="mt-3 max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">Let’s make this right.</h1><p class="mt-4 max-w-xl text-lg leading-8 text-stone-600">Tell us what happened with your order. We’ll review your request and let you know what happens next.</p>
      <div class="mt-10 grid gap-4 sm:grid-cols-3"><div class="rounded-xl border border-stone-200 bg-white p-4"><span class="text-xs font-semibold text-stone-400">01</span><p class="mt-2 text-sm font-semibold">Choose your order</p></div><div class="rounded-xl border border-stone-200 bg-white p-4"><span class="text-xs font-semibold text-stone-400">02</span><p class="mt-2 text-sm font-semibold">Tell us what happened</p></div><div class="rounded-xl border border-stone-200 bg-white p-4"><span class="text-xs font-semibold text-stone-400">03</span><p class="mt-2 text-sm font-semibold">Get a clear decision</p></div></div>
      <div class="mt-8 rounded-xl bg-amber-50 p-5 text-sm leading-6 text-amber-950"><strong>Refunds are usually available within 30 days.</strong> Final-sale items aren’t eligible. Requests above $500 are sent to a support specialist.</div>
    </section>
    <form @submit.prevent="submit" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-7">
      <h2 class="text-xl font-semibold">Start a refund request</h2><p class="mt-1 text-sm text-stone-500">A few details help us review this quickly.</p>
      <label class="mt-6 block text-sm font-medium">Customer
        <select v-model="form.customer_id" @change="changeCustomer" class="mt-2 w-full rounded-lg border-stone-300 bg-white text-sm"><option value="">Select a customer</option><option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} · {{ c.email }}</option>
        </select>
      </label>

      <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
      <label class="mt-5 block text-sm font-medium">Order
        <select v-model="form.order_id" :disabled="!customer" class="mt-2 w-full rounded-lg border-stone-300 bg-white text-sm disabled:bg-stone-100"><option value="">{{ customer ? 'Select an order' : 'Select a customer first' }}</option><option v-for="o in orders" :key="o.id" :value="o.id">{{ o.order_number }} · {{ new Date(o.order_date).toLocaleDateString() }} · {{ o.currency }} {{ Number(o.total_amount).toFixed(2) }}</option>
        </select>
      </label>
      <div v-if="order" class="mt-3 rounded-lg bg-stone-50 p-3 text-xs text-stone-600"><p class="font-semibold text-stone-800">{{ order.status }} · {{ order.items.map(i => i.product_name).join(', ') }}</p><p class="mt-1">{{ order.items.some(i => i.final_sale) ? 'Includes a final-sale item' : 'Standard return policy applies' }}</p></div>
      <label class="mt-5 block text-sm font-medium">What happened?
        <textarea v-model="form.message" rows="5" maxlength="3000" placeholder="My headphones arrived damaged and I would like a refund." class="mt-2 w-full resize-y rounded-lg border-stone-300 text-sm placeholder:text-stone-400 focus:border-amber-500 focus:ring-amber-500"></textarea>
      </label><p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
      <label class="mt-4 block text-sm font-medium">Requested amount <span class="font-normal text-stone-400">(optional)</span><div class="mt-2 flex items-center rounded-lg border border-stone-300 px-3"><span class="text-stone-400">$</span><input v-model="form.requested_amount" type="number" min="0.01" step="0.01" placeholder="Full order amount" class="w-full border-0 text-sm focus:ring-0"></div></label>
      <button :disabled="form.processing" class="mt-6 w-full rounded-lg bg-stone-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60">{{ form.processing ? 'Reviewing your request…' : 'Submit refund request' }}</button>
      <p class="mt-3 text-center text-xs text-stone-400">Your information is used only to review this request.</p>
    </form>
  </div>
</AppLayout>
</template>
