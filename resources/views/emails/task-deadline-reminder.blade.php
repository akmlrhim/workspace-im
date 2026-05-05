<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $urgency === 'today' ? 'Deadline Hari Ini' : 'Pengingat Deadline' }}</title>
</head>

<body
  style="margin:0;padding:0;background:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#333333;">

  @php
    $isCritical = $urgency === 'today';
    $space = $task->taskList?->space;
    $list = $task->taskList;
    $boardUrl = $list && $space ? route('project-management.lists.board', [$space, $list]) : config('app.url');
    $dueDateStr = $task->due_date->translatedFormat('l, d F Y');
    $priorityLabels = ['urgent' => 'Urgent', 'high' => 'Tinggi', 'normal' => 'Normal', 'low' => 'Rendah'];
    $priorityLabel = $priorityLabels[$task->priority] ?? 'Normal';
    $otherAssignees = $task->assignees->reject(fn($u) => $u->id === $recipient->id);
  @endphp

  <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

          {{-- App name --}}
          <tr>
            <td style="padding-bottom:20px;">
              <span style="font-size:13px;font-weight:600;color:#555555;">{{ config('app.name') }}</span>
            </td>
          </tr>

          {{-- Card --}}
          <tr>
            <td style="background:#ffffff;border:1px solid #e0e0e0;border-radius:6px;padding:32px 36px;">

              {{-- Subject label --}}
              <p
                style="margin:0 0 20px;font-size:12px;font-weight:600;color:#888888;text-transform:uppercase;letter-spacing:.06em;">
                {{ $isCritical ? 'Deadline Hari Ini' : 'Pengingat Deadline' }}
              </p>

              {{-- Greeting --}}
              <p style="margin:0 0 8px;">Halo <strong>{{ $recipient->name }}</strong>,</p>

              <p style="margin:0 0 24px;color:#555555;">
                @if ($isCritical)
                  Tugas berikut jatuh tempo <strong>hari ini</strong>. Segera selesaikan sebelum akhir hari.
                @else
                  Tugas berikut akan jatuh tempo <strong>besok</strong>. Pastikan sudah disiapkan.
                @endif
              </p>

              {{-- Divider --}}
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="border-top:1px solid #eeeeee;"></td>
                </tr>
              </table>

              {{-- Task detail --}}
              <p style="margin:0 0 6px;font-size:18px;font-weight:700;color:#111111;">{{ $task->title }}</p>

              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                <tr>
                  <td style="padding:2px 0;font-size:13px;color:#666666;">
                    <strong style="color:#333333;">Deadline</strong>&nbsp;&nbsp;
                    <span>{{ $dueDateStr }}{{ $isCritical ? ' — hari ini' : ' — besok' }}</span>
                  </td>
                </tr>
                @if ($space && $list)
                  <tr>
                    <td style="padding:2px 0;font-size:13px;color:#666666;">
                      <strong style="color:#333333;">List</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <span>{{ $space->name }} › {{ $list->name }}</span>
                    </td>
                  </tr>
                @endif
                <tr>
                  <td style="padding:2px 0;font-size:13px;color:#666666;">
                    <strong style="color:#333333;">Prioritas</strong>&nbsp;&nbsp;
                    <span>{{ $priorityLabel }}</span>
                  </td>
                </tr>
                @if ($otherAssignees->isNotEmpty())
                  <tr>
                    <td style="padding:2px 0;font-size:13px;color:#666666;">
                      <strong style="color:#333333;">Bersama</strong>&nbsp;&nbsp;
                      <span>{{ $otherAssignees->pluck('name')->join(', ') }}</span>
                    </td>
                  </tr>
                @endif
              </table>

              @if ($task->description)
                <p
                  style="margin:0 0 20px;font-size:13px;color:#777777;border-left:3px solid #dddddd;padding-left:12px;line-height:1.5;">
                  {{ Str::limit(strip_tags($task->description), 180) }}
                </p>
              @endif

              {{-- CTA --}}
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td style="border-radius:4px;background:#4f46e5;">
                    <a href="{{ $boardUrl }}" target="_blank"
                      style="display:inline-block;padding:10px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">
                      Buka Tugas
                    </a>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:20px 0 0;font-size:12px;color:#aaaaaa;text-align:center;line-height:1.7;">
              Email ini dikirim otomatis karena Anda ditugaskan pada tugas tersebut.<br>
              {{ config('app.name') }}
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>

</html>
