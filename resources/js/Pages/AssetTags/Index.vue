<script setup>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/components/data-table/data-table.vue";
import { columns, data } from "@/components/assettags/columns";
</script>
<template>
    <DashboardLayout :breadcrumbs="[{ name: 'Assets', href: '/assets' }, {name: 'Tags', href: '/assets/tags'}]">
        <DataTable :columns="columns" :data="data" />
    </DashboardLayout>
</template>
