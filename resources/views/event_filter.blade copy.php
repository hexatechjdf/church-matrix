<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events • Check-Ins | Planning Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf5ff 0%, #f0f9ff 50%, #e0f2fe 100%);
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.09);
            border-radius: 1.5rem;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            transition: all 0.3s ease;
        }

        .event-item:hover {
            background: rgba(139, 92, 246, 0.08);
            transform: translateX(6px);
        }

        .event-item.selected {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.12), transparent);
            border-left: 4px solid #8b5cf6;
        }

        .hero-title {
            background: linear-gradient(90deg, #1e293b 0%, #6d28d9 50%, #3b82f6 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        #filtersPanel {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(-10px);
        }

        #filtersPanel.show {
            max-height: 1000px;
            opacity: 1;
            transform: translateY(0);
            margin-top: 16px;
        }

        #filterChevron {
            transition: transform 0.4s ease;
        }

        #filterChevron.rotate-180 {
            transform: rotate(180deg);
        }
    </style>
</head>

<body class="">
    <main class="container-fluid mx-auto px-16 py-8">

        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="hero-title text-5xl font-black mb-2">Events</h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="glass p-6 card-hover rounded-2xl">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Total Events</p>
                        <p class="text-4xl font-bold">3</p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center"><i class="fas fa-calendar-alt text-2xl text-purple-600"></i></div>
                </div>
            </div>
            <div class="glass p-6 card-hover rounded-2xl">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600">Avg Attendance</p>
                        <p class="text-4xl font-bold">504.5</p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center"><i class="fas fa-users text-2xl text-blue-600"></i></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <div class="xl:col-span-4 space-y-6">

                <div class="glass p-6 rounded-3xl">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold flex items-center gap-3">
                            <i class="fas fa-calendar-check text-purple-600"></i> Events
                        </h2>
                        <div class="text-sm">
                            <button id="selectAllEvents" class="text-gray-500">All</button>
                            <span class="text-gray-400 mx-2">|</span>
                            <button id="clearAllEvents" class="text-gray-500">Clear</button>
                        </div>
                    </div>

                    <div class="" id="eventList">
                        @foreach($events as $event)
                        <label class="event-item p-4 flex items-center justify-between hover:bg-gray-50 cursor-pointer transition">
                            <div class="">
                                <div>
                                    <p class="font-bold text-lg">{{ $event->event_name }}</p>
                                </div>
                            </div>
                            <input type="checkbox" checked class="w-6 h-6 eventCheckbox" value="{{ $event->event_id }}">
                        </label>
                        @endforeach
                    </div>
                </div>

                <button
                    onclick="toggleFilters()"
                    class="w-full glass p-6 rounded-3xl flex items-center justify-between font-bold text-lg shadow-lg hover:shadow-xl transition-all bg-white/90 backdrop-blur">
                    <span class="flex items-center gap-3">
                        <i class="fas fa-sliders-h text-purple-600 text-xl"></i>
                        Filters
                    </span>
                    <i class="fas fa-chevron-down text-xl" id="filterChevron"></i>
                </button>

                <div id="filtersPanel" class="">
                    <div class="glass p-6 rounded-3xl space-y-7">
                        <h3 class="text-lg font-bold flex items-center gap-3 pb-4 border-b">
                            <i class="fas fa-filter text-purple-600"></i> Active Filters
                        </h3>

                        <div>
                            <p class="font-semibold mb-4">Group by</p>
                            <label class="flex items-center gap-3 mb-3"><input type="radio" name="group" checked class="w-5 h-5 text-purple-600"><span>Kind</span></label>
                            <label class="flex items-center gap-3"><input type="radio" name="group" class="w-5 h-5 text-purple-600"><span>Location</span></label>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold flex items-center gap-2">Locations</h4>
                                <button class="text-sm text-purple-600 hover:underline">Clear</button>
                            </div>
                            <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 cursor-pointer">
                                <input type="checkbox" checked class="w-5 h-5 rounded"><span>No location</span>
                            </label>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-semibold flex items-center gap-2"><i class="fas fa-clock text-blue-500"></i> Times</h4>
                                <button class="text-sm text-purple-600 hover:underline">Clear</button>
                            </div>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 cursor-pointer"><input type="checkbox" checked class="w-5 h-5 rounded"><span>9:00 am</span></label>
                                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 cursor-pointer"><input type="checkbox" checked class="w-5 h-5 rounded"><span>11:00 am</span></label>
                                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-purple-50 cursor-pointer"><input type="checkbox" checked class="w-5 h-5 rounded"><span>5:00 pm</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-8">
                <div class="glass p-8 shadow-2xl">
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <button id="datePickerBtn" onclick="toggleDateDropdown()"
                            class="bg-white border border-gray-300 rounded-xl px-6 py-3 flex items-center gap-3 font-medium hover:border-purple-500 transition-all shadow-sm">
                            <i class="fas fa-calendar-week text-purple-600"></i>
                            <span id="dateText">Last 30 days</span>
                            <i class="fas fa-chevron-down text-sm" id="dateChevron"></i>
                        </button>

                        <div id="dateDropdown"
                            class="absolute mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 hidden">

                            <a href="#" onclick="selectDate('Last 7 days')" class="block px-6 py-3 hover:bg-purple-50">Last 7 days</a>
                            <a href="#" onclick="selectDate('Last 30 days')" class="block px-6 py-3 hover:bg-purple-50">Last 30 days</a>
                            <a href="#" onclick="selectDate('Last year')" class="block px-6 py-3 hover:bg-purple-50">Last year</a>
                            <a href="#" onclick="selectDate('All time')" class="block px-6 py-3 hover:bg-purple-50">All time</a>
                        </div>


                        <input type="date" value="2025-11-11" class="border rounded-lg px-4 py-3">
                        <span class="text-gray-500">to</span>
                        <input type="date" value="2025-12-11" class="border rounded-lg px-4 py-3">

                        <div class="flex bg-white rounded-xl overflow-hidden shadow">
                            <button class="px-6 py-3 font-medium bg-purple-100 text-purple-700">Week</button>
                            <button class="px-6 py-3 font-medium text-gray-600 hover:bg-gray-100">Day</button>
                        </div>

                        <button class="bg-white border rounded-xl px-6 py-3 flex items-center gap-2 font-medium hover:shadow-lg transition">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>

                    <div class="bg-white/70 backdrop-blur rounded-2xl p-8 border border-gray-100">
                        <canvas id="attendanceChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleFilters() {
            const panel = document.getElementById('filtersPanel');
            const chevron = document.getElementById('filterChevron');

            panel.classList.toggle('show');
            chevron.classList.toggle('rotate-180');
        }

        function toggleDateDropdown() {
            const dropdown = document.getElementById('dateDropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectDate(text) {
            document.getElementById('dateText').textContent = text;
            toggleDateDropdown();
        }

        document.addEventListener('click', function(e) {
            const btn = document.getElementById('datePickerBtn');
            const dropdown = document.getElementById('dateDropdown');

            if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Nov 11', 'Nov 18', 'Nov 25', 'Dec 2', 'Dec 9', 'Dec 16'],
                datasets: [{
                    label: 'Attendance',
                    data: [480, 520, 490, 550, 510, 570],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#8b5cf6',
                    pointRadius: 6
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        document.getElementById('selectAllEvents').addEventListener('click', function() {
            document.querySelectorAll('.eventCheckbox').forEach(cb => cb.checked = true);
            updateChart(); 
        });

        document.getElementById('clearAllEvents').addEventListener('click', function() {
            document.querySelectorAll('.eventCheckbox').forEach(cb => cb.checked = false);
            updateChart();
        });

       

    </script>
</body>

</html>