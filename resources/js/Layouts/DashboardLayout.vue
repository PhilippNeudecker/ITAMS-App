<script lang="ts">
export const description
    = "A sidebar that collapses to icons."
export const iframeHeight = "800px"
export const containerClass = "w-full h-full"
</script>

<script setup lang="ts">
import AppSidebar from "@/components/AppSidebar.vue"
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/components/ui/breadcrumb"
import { Separator } from "@/components/ui/separator"
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from "@/components/ui/sidebar"

withDefaults(defineProps<{
    breadcrumbs?: { name: string, href: string }[]
}>(), {
    breadcrumbs: () => [],
})
</script>

<template>
    <SidebarProvider>
        <AppSidebar></AppSidebar>
        <SidebarInset class="h-svh">
            <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
                    <Breadcrumb v-if="breadcrumbs.length">
                        <BreadcrumbList>
                            <template v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.name">
                                <BreadcrumbSeparator class="hidden md:block" v-if="index !== 0" />
                                <BreadcrumbItem class="hidden md:block">
                                    <BreadcrumbPage v-if="index === breadcrumbs.length - 1">
                                        {{ breadcrumb.name }}
                                    </BreadcrumbPage>
                                    <BreadcrumbLink :href="breadcrumb.href" v-else>
                                    {{ breadcrumb.name }}
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            </template>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
            </header>
            <div class="flex flex-1 flex-col gap-4 p-4 pt-0 min-h-0">
                <slot :breadcrumbs="breadcrumbs" />
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
