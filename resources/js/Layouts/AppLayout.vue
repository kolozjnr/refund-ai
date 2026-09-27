<script setup>
import { Link } from '@inertiajs/vue3';
</script>
<template>
  <div class="min-h-screen">
    <header class="border-b border-stone-200 bg-white"><div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4"><Link href="/refunds" class="text-lg font-bold tracking-tight">DEMO<span class="text-amber-600">.</span></Link><nav class="flex gap-5 text-sm font-medium text-stone-600"><Link href="/refunds" class="hover:text-stone-950">Refunds</Link><Link href="/admin/refunds" class="hover:text-stone-950">Support dashboard</Link></nav></div></header>
    <main class="mx-auto max-w-6xl px-5 py-10"><slot /></main>
    <footer class="mx-auto max-w-6xl px-5 pb-8 text-xs text-stone-400">DEMO support · Decisions are reviewed against our refund policy</footer>
  </div>
</template>
