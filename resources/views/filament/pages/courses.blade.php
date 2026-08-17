<x-filament-panels::page>

    <style>
        .courses-container {
            display: flex;
            gap: 24px;
            width: 100%;
        }

        .course-card {
            flex: 1;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .course-card h3 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .course-card p {
            color: #6b7280;
        }
    </style>

    <div class="courses-container">

        <div class="course-card">
            <h3>Laravel</h3>
            <p>Learn Laravel framework.</p>
        </div>

        <div class="course-card">
            <h3>PHP</h3>
            <p>Learn PHP programming.</p>
        </div>

        <div class="course-card">
            <h3>Filament</h3>
            <p>Build admin panels with Filament.</p>
        </div>

    </div>

</x-filament-panels::page>