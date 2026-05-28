<template>
  <v-container fluid>
    <v-row class="mb-4">
      <v-col cols="12">
        <h1 class="text-h4">{{ t('app.calendar') }}</h1>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="4" lg="3">
        <v-card variant="outlined" class="mb-4">
          <v-card-text>
            <v-form @submit.prevent="handleSubmit">
              <v-text-field
                v-model="newEvent.event_name"
                :label="t('calendar.eventName')"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />
              <v-text-field
                v-model="newEvent.start_date"
                type="date"
                :label="t('calendar.startDate')"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />
              <v-text-field
                v-model="newEvent.end_date"
                type="date"
                :label="t('calendar.endDate')"
                variant="outlined"
                density="compact"
                class="mb-3"
                hide-details="auto"
              />
              <template v-if="addingMode">
                <v-btn color="primary" type="submit" block>
                  {{ t('calendar.addEvent') }}
                </v-btn>
              </template>
              <template v-else>
                <v-row>
                  <v-col cols="6">
                    <v-btn color="success" type="submit" block>
                      {{ t('app.common.update') }}
                    </v-btn>
                  </v-col>
                  <v-col cols="6">
                    <v-btn color="error" @click="deleteEvent" block>
                      {{ t('app.common.delete') }}
                    </v-btn>
                  </v-col>
                </v-row>
                <v-btn variant="text" @click="cancelEdit" block class="mt-2">
                  {{ t('app.common.cancel') }}
                </v-btn>
              </template>
            </v-form>
          </v-card-text>
        </v-card>

        <v-card variant="outlined">
          <v-card-text>
            <div class="text-subtitle-2 mb-2">
              {{ t('calendar.upcomingEvents') }} ({{ currentEvents.length }})
            </div>
            <v-list density="compact">
              <v-list-item
                v-for="event in currentEvents"
                :key="event.id"
                density="compact"
              >
                <template #title>
                  <span class="text-caption">{{ event.startStr }}</span>
                </template>
                <template #subtitle>
                  {{ event.title }}
                </template>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="8" lg="9">
        <v-card variant="outlined">
          <div ref="calendarEl" style="padding: 1rem; min-height: 500px">
            <v-alert type="info" variant="tonal">
              {{ t('calendar.fullCalendarNote') }}
            </v-alert>
            <FullCalendar
              v-if="showCalendar"
              :options="calendarOptions"
              class="demo-app-calendar"
            >
              <template v-slot:eventContent="arg">
                <b>{{ arg.timeText }}</b>
                <i>{{ arg.event.title }}</i>
              </template>
            </FullCalendar>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api.service'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'


const { t } = useI18n()

const calendarEl = ref(null)
const showCalendar = ref(true)

const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  initialView: 'dayGridMonth',
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  events: [],
  select: handleDateSelect,
  eventClick: handleEventClick,
  eventsSet: handleEvents
})

const currentEvents = ref([])

const events = ref([])
const newEvent = ref({
  event_name: '',
  start_date: '',
  end_date: ''
})
const addingMode = ref(true)
const indexToUpdate = ref('')

function handleDateSelect(selectInfo) {
  let title = prompt(t('calendar.enterEventName'))
  let calendarApi = selectInfo.view.calendar
  calendarApi.unselect()

  if (title) {
    calendarApi.addEvent({
      id: String(Date.now()),
      title,
      start: selectInfo.startStr,
      end: selectInfo.endStr,
      allDay: selectInfo.allDay
    })
  }
}

function handleEventClick(clickInfo) {
  const id = clickInfo.event.id
  const found = events.value.find(e => String(e.id) === String(id))
  if (found) {
    addingMode.value = false
    indexToUpdate.value = found.id
    newEvent.value = {
      event_name: found.title,
      start_date: found.start,
      end_date: found.end
    }
  }
}

function handleEvents(evts) {
  currentEvents.value = evts
}

function handleWeekendsToggle() {
  calendarOptions.value.weekends = !calendarOptions.value.weekends
}

function handleSubmit() {
  if (addingMode.value) {
    addNewEvent()
  } else {
    updateEvent()
  }
}

function addNewEvent() {
  api
    .post('/api/calendar', { ...newEvent.value })
    .then(() => {
      getEvents()
      resetForm()
    })
    .catch(err => console.log('Unable to add new event!', err.response?.data))
}

function updateEvent() {
  api
    .put('/api/calendar/' + indexToUpdate.value, { ...newEvent.value })
    .then(() => {
      resetForm()
      getEvents()
      addingMode.value = true
    })
    .catch(err => console.log('Unable to update event!', err.response?.data))
}

function deleteEvent() {
  if (confirm(t('calendar.confirmDelete'))) {
    api
      .delete('/api/calendar/' + indexToUpdate.value)
      .then(() => {
        resetForm()
        getEvents()
        addingMode.value = true
      })
      .catch(err => console.log('Unable to delete event!', err.response?.data))
  }
}

function getEvents() {
  api
    .get('/api/calendar')
    .then(resp => {
      events.value = resp.data?.data || []
      calendarOptions.value.events = events.value
    })
    .catch(err => console.log(err.response?.data))
}

function resetForm() {
  newEvent.value = { event_name: '', start_date: '', end_date: '' }
}

function cancelEdit() {
  resetForm()
  addingMode.value = true
}
</script>

<style>
.demo-app-calendar {
  max-width: 1100px;
  margin: 0 auto;
}
</style>
