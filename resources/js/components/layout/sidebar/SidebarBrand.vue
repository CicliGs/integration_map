<template>
    <div class="sidebar__brand">
        <p class="sidebar__account">{{ accountName }}</p>
    </div>
</template>

<script setup>
defineProps({
    accountName: { type: String, default: 'Аккаунт' },
});
</script>
