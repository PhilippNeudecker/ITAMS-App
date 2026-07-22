<script setup lang="ts">
import type { SidebarProps } from "@/components/ui/sidebar"

import {
  AudioWaveform,
  BookOpen,
  Bot,
  Command,
  Frame,
  GalleryVerticalEnd,
  Laptop,
  Map,
  PieChart,
  Settings2,
  SquareTerminal,
} from "@lucide/vue"
import NavMain from "@/components/NavMain.vue"
import NavProjects from "@/components/NavProjects.vue"
import NavUser from "@/components/NavUser.vue"
import TeamSwitcher from "@/components/TeamSwitcher.vue"
import SidebarLogo from "@/components/SidebarLogo.vue"
import { route } from 'ziggy-js';

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from "@/components/ui/sidebar"
import { PhLaptop } from "@phosphor-icons/vue"

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: "icon",
})

// This is sample data.
const data = {
  user: {
    name: "Philipp Neudecker",
    pid: 70158,
    email: "philipp.neudecker@soldan.com",
    avatar: "/avatars/philipp.jpg",
  },
  navMain: [
    {
      title: "Assets",
      url: "#",
      icon: Laptop,
      isActive: true,
      items: [
        {
          title: "Übersicht",
          url: route('assets.index'),
        },
        {
            title: "Tags",
            url: route('assets.tags.index'),
        },
        {
            title: "Kategorien",
            url: route('assets.categories.index'),
        },
        {
            title: "Hersteller",
            url: route('assets.manufacturers.index'),
        },
      ],
    },
    {
      title: "Systemeinstellungen",
      url: "#",
      icon: Settings2,
      items: [
        {
            title: "Lagerorte",
            url: route('locations.index'),
        },
        {
            title: "Status Definitionen",
            url: route('statusdefinitions.index'),
        },
        {
            title: "Lagerartdefinitionen",
            url: route('locationtypedefinitions.index'),
        },
        {
            title: "Kostenstellen",
            url: route('costcenters.index'),
        },
        {
            title: "Mitarbeiter",
            url: route('employees.index'),
        },
        // {
        //   title: "Allgemein",
        //   url: "#",
        // },
        // {
        //   title: "Assets",
        //   url: "#",
        // },
        // {
        //   title: "Billing",
        //   url: "#",
        // },
        // {
        //   title: "Limits",
        //   url: "#",
        // },
      ],
    },
  ],
  projects: [
    {
      name: "Design Engineering",
      url: "#",
      icon: Frame,
    },
    {
      name: "Sales & Marketing",
      url: "#",
      icon: PieChart,
    },
    {
      name: "Travel",
      url: "#",
      icon: Map,
    },
  ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <SidebarLogo />
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="data.navMain" />
      <!-- <NavProjects :projects="data.projects" /> -->
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="data.user" />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>
