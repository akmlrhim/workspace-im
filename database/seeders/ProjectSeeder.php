<?php

namespace Database\Seeders;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskLabel;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProjectSeeder extends Seeder
{
    /**
     * @var array<string, TaskLabel>
     */
    private array $labels = [];

    public function run(): void
    {
        $superUser = User::where('email', 'superuser@project.test')->first();
        $admin = User::where('email', 'admin@project.test')->first();
        $manager = User::where('email', 'manager@project.test')->first();
        $member = User::where('email', 'member@project.test')->first();

        if (! $admin) {
            $this->command->warn('Admin user tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');

            return;
        }

        $allUsers = collect([$superUser, $admin, $manager, $member])->filter()->values();

        $workspace = Workspace::firstOrCreate(
            ['owner_id' => $admin->id],
            ['name' => 'PT Inovasi Mandiri']
        );

        foreach ($allUsers as $user) {
            WorkspaceMember::firstOrCreate(
                ['workspace_id' => $workspace->id, 'user_id' => $user->id],
                ['role' => $user->id === $admin->id ? 'owner' : 'member']
            );
        }

        $this->labels = $this->createLabels($workspace->id);

        $this->seedDivisi($workspace, $admin, $allUsers);
        $this->seedProyek($workspace, $admin, $allUsers);
        $this->seedHQ($workspace, $admin, $allUsers);

        $this->command->info('✅ Project seeder selesai: 3 spaces (Divisi, Proyek, HQ) dengan lists & tasks lengkap.');
    }

    private function seedDivisi(Workspace $workspace, User $admin, Collection $allUsers): void
    {
        $space = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'divisi'],
            ['name' => 'Divisi', 'color' => '#8b5cf6', 'icon' => 'building-office-2', 'position' => 0]
        );

        $this->command->line('  → Seeding space: Divisi');

        $hr = $this->makeList($space, 'HR & Rekrutmen', 0);
        $hr->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $hrStatus = $hr->statuses()->orderBy('position')->get();

        $this->seedTasks($hr, $hrStatus, $admin, $allUsers, [
            ['title' => 'Buat job description Senior Developer', 'priority' => 'high', 'si' => 3, 'due' => -5, 'labels' => ['Dokumentasi']],
            ['title' => 'Posting lowongan di LinkedIn & JobStreet', 'priority' => 'high', 'si' => 3, 'due' => -3],
            ['title' => 'Screening 30 CV pelamar', 'priority' => 'normal', 'si' => 2, 'due' => -1],
            ['title' => 'Jadwalkan interview tahap 1', 'priority' => 'normal', 'si' => 1, 'due' => 2],
            ['title' => 'Lakukan background check kandidat terpilih', 'priority' => 'high', 'si' => 1, 'due' => 5],
            ['title' => 'Siapkan kontrak kerja karyawan baru', 'priority' => 'normal', 'si' => 0, 'due' => 7, 'labels' => ['Dokumentasi', 'Legal']],
            ['title' => 'Orientasi karyawan baru batch Mei 2026', 'priority' => 'normal', 'si' => 0, 'due' => 10],
            ['title' => 'Update database rekrutmen internal', 'priority' => 'low', 'si' => 0, 'due' => 14],
            ['title' => 'Rekap biaya rekrutmen Q1 2026', 'priority' => 'normal', 'si' => 3, 'due' => -7, 'labels' => ['Keuangan']],
        ]);

        $evaluasi = $this->makeList($space, 'Evaluasi Kinerja', 1);
        $evaluasi->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $evalStatus = $evaluasi->statuses()->orderBy('position')->get();

        $this->seedTasks($evaluasi, $evalStatus, $admin, $allUsers, [
            ['title' => 'Distribusi form KPI Q1 2026', 'priority' => 'high', 'si' => 3, 'due' => -10, 'labels' => ['Dokumentasi']],
            ['title' => 'Rekap hasil evaluasi kinerja Q1 2026', 'priority' => 'high', 'si' => 2, 'due' => -2],
            ['title' => 'Meeting 1-on-1 dengan seluruh team lead', 'priority' => 'normal', 'si' => 1, 'due' => 3],
            ['title' => 'Siapkan laporan kinerja divisi Q1 2026', 'priority' => 'normal', 'si' => 1, 'due' => 5, 'labels' => ['Dokumentasi']],
            ['title' => 'Finalisasi dan approval bonus Q1 2026', 'priority' => 'urgent', 'si' => 0, 'due' => 7, 'labels' => ['Mendesak', 'Keuangan']],
            ['title' => 'Tetapkan target KPI Q2 2026 per divisi', 'priority' => 'normal', 'si' => 0, 'due' => 14],
            ['title' => 'Buat template form evaluasi kinerja', 'priority' => 'low', 'si' => 3, 'due' => -20, 'labels' => ['Dokumentasi']],
            ['title' => 'Sosialisasi sistem penilaian baru ke karyawan', 'priority' => 'normal', 'si' => 0, 'due' => 21],
        ]);

        $sdm = $this->makeList($space, 'Pengembangan SDM', 2);
        $sdm->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $sdmStatus = $sdm->statuses()->orderBy('position')->get();

        $this->seedTasks($sdm, $sdmStatus, $admin, $allUsers, [
            ['title' => 'Identifikasi kebutuhan pelatihan 2026', 'priority' => 'normal', 'si' => 3, 'due' => -20, 'labels' => ['Dokumentasi']],
            ['title' => 'Daftarkan tim ke training Agile PM', 'priority' => 'high', 'si' => 2, 'due' => -5],
            ['title' => 'Workshop komunikasi efektif — 20 Mei', 'priority' => 'normal', 'si' => 1, 'due' => 7],
            ['title' => 'Program sertifikasi ISO 9001 tim QA', 'priority' => 'high', 'si' => 0, 'due' => 21, 'labels' => ['Peningkatan', 'Mendesak']],
            ['title' => 'Buat modul onboarding digital interaktif', 'priority' => 'normal', 'si' => 0, 'due' => 30, 'labels' => ['Fitur Baru']],
            ['title' => 'Evaluasi efektivitas pelatihan Q1 2026', 'priority' => 'normal', 'si' => 3, 'due' => -15, 'labels' => ['Dokumentasi']],
            ['title' => 'Rencanakan program mentoring internal', 'priority' => 'low', 'si' => 0, 'due' => 35],
            ['title' => 'Anggaran pelatihan Q2 2026', 'priority' => 'high', 'si' => 1, 'due' => 5, 'labels' => ['Keuangan']],
        ]);

        $admin2 = $this->makeList($space, 'Administrasi Divisi', 3);
        $admin2->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $admStatus = $admin2->statuses()->orderBy('position')->get();

        $this->seedTasks($admin2, $admStatus, $admin, $allUsers, [
            ['title' => 'Update struktur organisasi perusahaan', 'priority' => 'normal', 'si' => 3, 'due' => -7, 'labels' => ['Dokumentasi']],
            ['title' => 'Rekap absensi dan lembur April 2026', 'priority' => 'high', 'si' => 2, 'due' => -1],
            ['title' => 'Pengajuan cuti tahunan karyawan', 'priority' => 'normal', 'si' => 1, 'due' => 3],
            ['title' => 'Update SOP rekrutmen dan onboarding', 'priority' => 'normal', 'si' => 1, 'due' => 10, 'labels' => ['Dokumentasi', 'Peningkatan']],
            ['title' => 'Laporan headcount bulanan ke direksi', 'priority' => 'high', 'si' => 0, 'due' => 7],
            ['title' => 'Inventarisasi aset divisi 2026', 'priority' => 'low', 'si' => 0, 'due' => 21],
        ]);
    }

    private function seedProyek(Workspace $workspace, User $admin, Collection $allUsers): void
    {
        $space = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'proyek'],
            ['name' => 'Proyek', 'color' => '#f59e0b', 'icon' => 'rocket-launch', 'position' => 1]
        );

        $this->command->line('  → Seeding space: Proyek');

        $web = $this->makeList($space, 'Website Redesign', 0);
        $web->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $webStatus = $web->statuses()->orderBy('position')->get();

        $this->seedTasks($web, $webStatus, $admin, $allUsers, [
            ['title' => 'Analisis kebutuhan dan riset pengguna', 'priority' => 'normal', 'si' => 3, 'due' => -20, 'labels' => ['Dokumentasi']],
            ['title' => 'Buat wireframe halaman utama & produk', 'priority' => 'high', 'si' => 3, 'due' => -15, 'labels' => ['Desain']],
            ['title' => 'Desain UI kit dan sistem komponen', 'priority' => 'high', 'si' => 2, 'due' => -3, 'labels' => ['Desain']],
            ['title' => 'Implementasi halaman beranda (homepage)', 'priority' => 'high', 'si' => 1, 'due' => 5, 'labels' => ['Fitur Baru']],
            ['title' => 'Implementasi halaman produk & katalog', 'priority' => 'normal', 'si' => 1, 'due' => 10, 'labels' => ['Fitur Baru']],
            ['title' => 'Integrasi CMS dan manajemen konten', 'priority' => 'normal', 'si' => 0, 'due' => 14, 'labels' => ['Fitur Baru']],
            ['title' => 'Testing cross-browser & responsif', 'priority' => 'high', 'si' => 0, 'due' => 21, 'labels' => ['Peningkatan']],
            ['title' => 'Deployment ke staging dan review', 'priority' => 'urgent', 'si' => 0, 'due' => 25, 'labels' => ['Mendesak']],
            ['title' => 'SEO optimization halaman-halaman utama', 'priority' => 'normal', 'si' => 0, 'due' => 28, 'labels' => ['Peningkatan']],
            ['title' => 'Go-live website baru', 'priority' => 'urgent', 'si' => 0, 'due' => 35, 'labels' => ['Mendesak']],
        ]);

        $mobile = $this->makeList($space, 'Mobile App Development', 1);
        $mobile->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $mobileStatus = $mobile->statuses()->orderBy('position')->get();

        $this->seedTasks($mobile, $mobileStatus, $admin, $allUsers, [
            ['title' => 'Dokumentasi kebutuhan fitur mobile app', 'priority' => 'normal', 'si' => 3, 'due' => -25, 'labels' => ['Dokumentasi']],
            ['title' => 'Desain UI/UX aplikasi mobile', 'priority' => 'high', 'si' => 3, 'due' => -10, 'labels' => ['Desain']],
            ['title' => 'Setup project React Native & environment', 'priority' => 'normal', 'si' => 2, 'due' => -5],
            ['title' => 'Implementasi modul autentikasi & login', 'priority' => 'high', 'si' => 1, 'due' => 7, 'labels' => ['Fitur Baru']],
            ['title' => 'Implementasi dashboard dan statistik', 'priority' => 'normal', 'si' => 1, 'due' => 14, 'labels' => ['Fitur Baru']],
            ['title' => 'Integrasi API backend dengan mobile', 'priority' => 'high', 'si' => 0, 'due' => 20],
            ['title' => 'Push notification & real-time updates', 'priority' => 'normal', 'si' => 0, 'due' => 25, 'labels' => ['Fitur Baru']],
            ['title' => 'Testing di berbagai device & OS', 'priority' => 'high', 'si' => 0, 'due' => 28],
            ['title' => 'Submit ke Google Play Store & App Store', 'priority' => 'urgent', 'si' => 0, 'due' => 35, 'labels' => ['Mendesak']],
        ]);

        $proj = $this->makeList($space, 'Implementasi Project', 2);
        $proj->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $projStatus = $proj->statuses()->orderBy('position')->get();

        $this->seedTasks($proj, $projStatus, $admin, $allUsers, [
            ['title' => 'Analisis proses bisnis eksisting perusahaan', 'priority' => 'urgent', 'si' => 3, 'due' => -30, 'labels' => ['Dokumentasi']],
            ['title' => 'Mapping kebutuhan modul ERP per divisi', 'priority' => 'high', 'si' => 3, 'due' => -20, 'labels' => ['Dokumentasi']],
            ['title' => 'Konfigurasi modul keuangan & akuntansi', 'priority' => 'high', 'si' => 2, 'due' => -5],
            ['title' => 'Konfigurasi modul HR & payroll', 'priority' => 'high', 'si' => 2, 'due' => -3],
            ['title' => 'Migrasi data dari sistem lama ke ERP', 'priority' => 'urgent', 'si' => 1, 'due' => 5, 'labels' => ['Mendesak']],
            ['title' => 'Pelatihan user modul keuangan', 'priority' => 'normal', 'si' => 1, 'due' => 10],
            ['title' => 'Pelatihan user modul HR & payroll', 'priority' => 'normal', 'si' => 0, 'due' => 12],
            ['title' => 'UAT (User Acceptance Testing) modul utama', 'priority' => 'high', 'si' => 0, 'due' => 15, 'labels' => ['Peningkatan']],
            ['title' => 'Perbaikan bug hasil UAT', 'priority' => 'high', 'si' => 0, 'due' => 19, 'labels' => ['Bug', 'Mendesak']],
            ['title' => 'Go-live ERP phase 1', 'priority' => 'urgent', 'si' => 0, 'due' => 21, 'labels' => ['Mendesak']],
        ]);

        $qa = $this->makeList($space, 'QA & Testing', 3);
        $qa->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $qaStatus = $qa->statuses()->orderBy('position')->get();

        $this->seedTasks($qa, $qaStatus, $admin, $allUsers, [
            ['title' => 'Buat test plan Q2 2026', 'priority' => 'high', 'si' => 3, 'due' => -15, 'labels' => ['Dokumentasi']],
            ['title' => 'Testing fitur autentikasi website', 'priority' => 'normal', 'si' => 3, 'due' => -7],
            ['title' => 'Testing integrasi API backend', 'priority' => 'normal', 'si' => 2, 'due' => -2],
            ['title' => 'Regression testing halaman website', 'priority' => 'high', 'si' => 1, 'due' => 5],
            ['title' => 'Security audit & penetration testing', 'priority' => 'urgent', 'si' => 0, 'due' => 10, 'labels' => ['Mendesak', 'Bug']],
            ['title' => 'Performance & load testing aplikasi', 'priority' => 'normal', 'si' => 0, 'due' => 12, 'labels' => ['Peningkatan']],
            ['title' => 'Buat laporan hasil testing Q2 2026', 'priority' => 'normal', 'si' => 0, 'due' => 21, 'labels' => ['Dokumentasi']],
        ]);
    }

    private function seedHQ(Workspace $workspace, User $admin, Collection $allUsers): void
    {
        $space = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'hq'],
            ['name' => 'HQ', 'color' => '#10b981', 'icon' => 'star', 'position' => 2]
        );

        $this->command->line('  → Seeding space: HQ');

        $legal = $this->makeList($space, 'Legal & Compliance', 0);
        $legal->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $legalStatus = $legal->statuses()->orderBy('position')->get();

        $this->seedTasks($legal, $legalStatus, $admin, $allUsers, [
            ['title' => 'Review kontrak vendor teknologi baru', 'priority' => 'high', 'si' => 2, 'due' => -3, 'labels' => ['Legal', 'Mendesak']],
            ['title' => 'Update kebijakan privasi data (PDPA)', 'priority' => 'urgent', 'si' => 1, 'due' => 7, 'labels' => ['Legal', 'Mendesak']],
            ['title' => 'Perpanjangan lisensi software suite', 'priority' => 'normal', 'si' => 1, 'due' => 14, 'labels' => ['Legal']],
            ['title' => 'Audit kepatuhan ISO 27001 tahunan', 'priority' => 'high', 'si' => 0, 'due' => 21, 'labels' => ['Legal', 'Peningkatan']],
            ['title' => 'Siapkan laporan pajak badan tahunan', 'priority' => 'urgent', 'si' => 0, 'due' => 28, 'labels' => ['Legal', 'Keuangan', 'Mendesak']],
            ['title' => 'Review perjanjian kerja sama distributor', 'priority' => 'normal', 'si' => 3, 'due' => -10, 'labels' => ['Legal']],
            ['title' => 'Finalisasi MOU kemitraan baru', 'priority' => 'high', 'si' => 0, 'due' => 30, 'labels' => ['Legal']],
        ]);

        $rapat = $this->makeList($space, 'Agenda & Rapat', 1);
        $rapat->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $rapatStatus = $rapat->statuses()->orderBy('position')->get();

        $this->seedTasks($rapat, $rapatStatus, $admin, $allUsers, [
            ['title' => 'Rapat board of directors Q2 2026', 'priority' => 'high', 'si' => 3, 'due' => -5, 'labels' => ['Dokumentasi']],
            ['title' => 'Town hall seluruh karyawan — April 2026', 'priority' => 'normal', 'si' => 3, 'due' => -2],
            ['title' => 'Review OKR Q1 2026 bersama direksi', 'priority' => 'high', 'si' => 2, 'due' => 3, 'labels' => ['Dokumentasi']],
            ['title' => 'Kick-off meeting Q2 2026 lintas divisi', 'priority' => 'normal', 'si' => 1, 'due' => 7],
            ['title' => 'Rapat evaluasi proyek strategis semua unit', 'priority' => 'high', 'si' => 0, 'due' => 14],
            ['title' => 'Siapkan materi presentasi direksi Q2', 'priority' => 'high', 'si' => 1, 'due' => 5, 'labels' => ['Dokumentasi']],
            ['title' => 'Annual General Meeting (AGM) 2026', 'priority' => 'urgent', 'si' => 0, 'due' => 45, 'labels' => ['Mendesak', 'Legal']],
            ['title' => 'Rapat koordinasi antar divisi bulanan', 'priority' => 'normal', 'si' => 0, 'due' => 10],
        ]);

        $keuangan = $this->makeList($space, 'Keuangan', 2);
        $keuangan->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $keuStatus = $keuangan->statuses()->orderBy('position')->get();

        $this->seedTasks($keuangan, $keuStatus, $admin, $allUsers, [
            ['title' => 'Rekap laporan keuangan April 2026', 'priority' => 'urgent', 'si' => 2, 'due' => -2, 'labels' => ['Keuangan', 'Mendesak']],
            ['title' => 'Review dan approval anggaran Q2 2026', 'priority' => 'high', 'si' => 1, 'due' => 5, 'labels' => ['Keuangan']],
            ['title' => 'Approval pembelian server & infrastruktur', 'priority' => 'high', 'si' => 1, 'due' => 7, 'labels' => ['Keuangan', 'Mendesak']],
            ['title' => 'Audit internal keuangan semester 1', 'priority' => 'normal', 'si' => 0, 'due' => 21, 'labels' => ['Keuangan']],
            ['title' => 'Presentasi laporan keuangan ke direksi', 'priority' => 'high', 'si' => 0, 'due' => 14, 'labels' => ['Keuangan', 'Dokumentasi']],
            ['title' => 'Rekonsiliasi bank Maret–April 2026', 'priority' => 'normal', 'si' => 3, 'due' => -7, 'labels' => ['Keuangan']],
            ['title' => 'Proyeksi arus kas Q3 2026', 'priority' => 'normal', 'si' => 0, 'due' => 28, 'labels' => ['Keuangan', 'Dokumentasi']],
        ]);

        $strategi = $this->makeList($space, 'Strategi & Planning', 3);
        $strategi->members()->syncWithoutDetaching($allUsers->pluck('id'));
        $strStatus = $strategi->statuses()->orderBy('position')->get();

        $this->seedTasks($strategi, $strStatus, $admin, $allUsers, [
            ['title' => 'Review & update visi misi perusahaan 2026', 'priority' => 'normal', 'si' => 3, 'due' => -30, 'labels' => ['Dokumentasi']],
            ['title' => 'OKR planning seluruh divisi Q2 2026', 'priority' => 'high', 'si' => 3, 'due' => -10, 'labels' => ['Dokumentasi']],
            ['title' => 'Analisis kompetitor & tren industri Q1', 'priority' => 'normal', 'si' => 2, 'due' => -5, 'labels' => ['Dokumentasi']],
            ['title' => 'Susun rencana ekspansi bisnis 2026–2027', 'priority' => 'high', 'si' => 1, 'due' => 30, 'labels' => ['Dokumentasi', 'Peningkatan']],
            ['title' => 'Review dan perbarui kemitraan strategis', 'priority' => 'normal', 'si' => 0, 'due' => 21],
            ['title' => 'Workshop inovasi produk bersama direksi', 'priority' => 'normal', 'si' => 0, 'due' => 28, 'labels' => ['Peningkatan']],
            ['title' => 'Evaluasi target bisnis semester 1 2026', 'priority' => 'high', 'si' => 0, 'due' => 14, 'labels' => ['Dokumentasi']],
        ]);
    }

    private function makeList(Space $space, string $name, int $position): TaskList
    {
        $list = TaskList::firstOrCreate(
            ['space_id' => $space->id, 'name' => $name],
            ['position' => $position]
        );

        if ($list->statuses()->count() === 0) {
            $list->createDefaultStatuses();
        }

        return $list;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection  $statuses
     * @param  array<int, array<string, mixed>>  $taskData
     */
    private function seedTasks(TaskList $list, $statuses, User $creator, Collection $allUsers, array $taskData): void
    {
        foreach ($taskData as $position => $t) {
            $statusId = $statuses[$t['si']]->id ?? $statuses->first()->id;
            $dueDate = isset($t['due']) ? now()->addDays((int) $t['due'])->toDateString() : null;

            $task = Task::firstOrCreate(
                ['task_list_id' => $list->id, 'title' => $t['title']],
                [
                    'task_status_id' => $statusId,
                    'priority' => $t['priority'],
                    'due_date' => $dueDate,
                    'position' => $position,
                    'created_by' => $creator->id,
                ]
            );

            if ($allUsers->isNotEmpty()) {
                $count = min($allUsers->count(), rand(1, 2));
                $task->assignees()->syncWithoutDetaching(
                    $allUsers->random($count)->pluck('id')
                );
            }

            if (! empty($t['labels'])) {
                $labelIds = collect($t['labels'])
                    ->map(fn ($name) => $this->labels[$name]->id ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                $task->labels()->syncWithoutDetaching($labelIds);
            }

            if ($task->wasRecentlyCreated) {
                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => $creator->id,
                    'type' => 'created',
                    'new_value' => $task->title,
                ]);
            }
        }
    }

    /**
     * @return array<string, TaskLabel>
     */
    private function createLabels(int $workspaceId): array
    {
        $labelData = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Fitur Baru', 'color' => '#3b82f6'],
            ['name' => 'Peningkatan', 'color' => '#10b981'],
            ['name' => 'Desain', 'color' => '#8b5cf6'],
            ['name' => 'Dokumentasi', 'color' => '#f59e0b'],
            ['name' => 'Mendesak', 'color' => '#dc2626'],
            ['name' => 'Legal', 'color' => '#0ea5e9'],
            ['name' => 'Keuangan', 'color' => '#14b8a6'],
        ];

        $labels = [];
        foreach ($labelData as $ld) {
            $labels[$ld['name']] = TaskLabel::firstOrCreate(
                ['workspace_id' => $workspaceId, 'name' => $ld['name']],
                ['color' => $ld['color']]
            );
        }

        return $labels;
    }
}
