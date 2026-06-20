import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('appointment-calendar');
    const filterEl = document.getElementById('veterinarian-filter');

    if (!calendarEl) {
        return;
    }

    const allEvents = JSON.parse(calendarEl.dataset.events || '[]');
    const createUrl = calendarEl.dataset.createUrl;

    const calendar = new Calendar(calendarEl, {
        plugins: [
            dayGridPlugin,
            timeGridPlugin,
            interactionPlugin,
        ],

        initialView: 'timeGridWeek',
        locale: 'es',
        selectable: true,
        allDaySlot: false,

        slotMinTime: '07:00:00',
        slotMaxTime: '19:00:00',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },

        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
        },

        events: allEvents,

        eventClick: function (info) {
            info.jsEvent.preventDefault();

            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },

        dateClick: function (info) {
            const date = info.dateStr.substring(0, 10);
            const time = info.dateStr.length > 10
                ? info.dateStr.substring(11, 16)
                : '08:00';

            window.location.href = `${createUrl}?appointment_date=${date}&appointment_time=${time}`;
        },
    });

    calendar.render();

    if (filterEl) {
        filterEl.addEventListener('change', function () {
            const veterinarianId = this.value;

            const filteredEvents = veterinarianId
                ? allEvents.filter(event => String(event.extendedProps.veterinarian_id) === String(veterinarianId))
                : allEvents;

            calendar.removeAllEvents();
            calendar.addEventSource(filteredEvents);
        });
    }
});