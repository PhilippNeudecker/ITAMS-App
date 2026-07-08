<template>
  <DashboardLayout>
    <div class="flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">Dashboard Overview</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Gesamtumsatz</CardTitle>
            <!-- <CurrencyEur :size="20" class="text-muted-foreground" /> -->
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">45.231,89 €</div>
            <p class="text-xs text-muted-foreground">+20.1% im Vergleich zum Vormonat</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Neue Benutzer</CardTitle>
            <!-- <UserPlus :size="20" class="text-muted-foreground" /> -->
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">+2,350</div>
            <p class="text-xs text-muted-foreground">+180.1% im Vergleich zum Vormonat</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Aktive Sitzungen</CardTitle>
            <!-- <Activity :size="20" class="text-muted-foreground" /> -->
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">+12,234</div>
            <p class="text-xs text-muted-foreground">+19% im Vergleich zur letzten Stunde</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Server Status</CardTitle>
            <!-- <CheckCircle :size="20" class="text-emerald-500" /> -->
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">99.98%</div>
            <p class="text-xs text-muted-foreground">Alle Systeme laufen stabil</p>
          </CardContent>
        </Card>
      </div>

      <Card class="col-span-4">
        <CardHeader>
          <CardTitle>Letzte Transaktionen</CardTitle>
          <CardDescription>Übersicht der jüngsten Aktivitäten im ITAMS-System.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Kunde</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Datum</TableHead>
                <TableHead class="text-right">Betrag</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="tx in transactions" :key="tx.id">
                <TableCell class="font-medium">{{ tx.name }}</TableCell>
                <TableCell>{{ tx.status }}</TableCell>
                <TableCell>{{ tx.date }}</TableCell>
                <TableCell class="text-right">{{ tx.amount }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from "@/Layouts/DashboardLayout.vue"
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

// Phosphor Icons
// import { CurrencyEur, UserPlus, Activity, CheckCircle } from "@phosphor-icons/vue"

// Dummy-Daten für die Tabelle
const transactions = [
  { id: 1, name: "Max Mustermann", status: "Erfolgreich", date: "2026-06-03", amount: "250,00 €" },
  { id: 2, name: "Erika Mustermann", status: "Ausstehend", date: "2026-06-02", amount: "89,90 €" },
  { id: 3, name: "John Doe", status: "Fehlgeschlagen", date: "2026-05-30", amount: "1.200,00 €" },
]
</script>
