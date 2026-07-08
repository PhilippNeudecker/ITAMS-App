<template>
    <SidebarProvider :defaultOpen="true">
        <div class="flex min-h-screen w-full bg-slate-50/50">

            <Sidebar collapsible="icon" class="peer">
                <SidebarHeader class="border-b p-4 flex flex-row items-center gap-2 bg-background">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-primary-foreground">
                        <img src="../../img/ITAMS Logo Short.png" alt="ITAMS" class="h-8 w-auto" />
                    </div>
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-primary-foreground">
                        <img src="../../img/ITAMS Logo.png" alt="ITAMS" class="h-8 w-auto group-data-[state=collapsed]:hidden" />
                    </div>
                </SidebarHeader>

                <SidebarContent class="p-2 bg-background">
                    <SidebarGroup>
                        <SidebarGroupLabel class="group-data-[state=collapsed]:hidden">Platform</SidebarGroupLabel>
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <SidebarMenuButton as-child isActive>
                                    <a href="#" class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded bg-primary/10 shrink-0 flex items-center justify-center text-xs">D</div>
                                        <span class="group-data-[state=collapsed]:hidden">Dashboard</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton as-child>
                                    <a href="#" class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded bg-muted shrink-0 flex items-center justify-center text-xs">B</div>
                                        <span class="group-data-[state=collapsed]:hidden">Benutzer</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                            <SidebarMenuItem>
                                <SidebarMenuButton as-child>
                                    <a href="#" class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded bg-muted shrink-0 flex items-center justify-center text-xs">E</div>
                                        <span class="group-data-[state=collapsed]:hidden">Einstellungen</span>
                                    </a>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroup>
                </SidebarContent>
            </Sidebar>

            <div class="flex flex-1 flex-col min-w-0 transition-all duration-300 ease-in-out md:peer-data-[state=open]:pl-64 md:peer-data-[state=collapsed]:pl-12">

                <header class="flex h-16 items-center gap-4 border-b bg-background px-6 sticky top-0 z-10">
                    <SidebarTrigger />
                    <div class="w-full flex-1">
                        <div class="relative w-full max-w-sm">
                            <input type="search" placeholder="Suchen..."
                                class="w-full rounded-lg bg-muted/70 pl-4 pr-4 py-2 text-sm border-none focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all" />
                        </div>
                    </div>
                    <button class="relative rounded-full p-2 hover:bg-muted transition-colors">
                        <div class="w-2 h-2 rounded-full bg-primary absolute top-2 right-2"></div>
                        <span class="text-xs block opacity-50">🔔</span>
                    </button>
                </header>

                <main class="flex-1 p-6 md:p-8 space-y-6">
                    <slot />
                </main>
            </div>

        </div>
    </SidebarProvider>
</template>

<script setup>
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarTrigger
} from "@/components/ui/sidebar"
</script>
