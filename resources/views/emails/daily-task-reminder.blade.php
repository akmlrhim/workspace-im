<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daily Task Belum Selesai</title>
</head>

<body
  style="margin:0;padding:0;background:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#333333;">

  @php
    $space = $taskList->space;
    $listUrl = $space ? route('project-management.lists.show', [$space, $taskList]) : config('app.url');
    $todayStr = \Illuminate\Support\Carbon::today()->translatedFormat('l, d F Y');
    $pendingCount = $pendingTasks->count();
  @endphp

  <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">

          <tr>
            <td style="padding-bottom:20px;">
              <span style="font-size:13px;font-weight:600;color:#555555;">{{ config('app.name') }}</span>
            </td>
          </tr>

          <tr>
            <td style="background:#ffffff;border:1px solid #e0e0e0;border-radius:6px;padding:32px 36px;">

              <p
                style="margin:0 0 20px;font-size:12px;font-weight:600;color:#b45309;text-transform:uppercase;letter-spacing:.06em;">
                Daily Task Belum Selesai
              </p>

              <p style="margin:0 0 8px;">Halo <strong>{{ $recipient->name }}</strong>,</p>

              <p style="margin:0 0 24px;color:#555555;">
                Kamu masih memiliki <strong>{{ $pendingCount }} daily task</strong> yang belum diselesaikan hari ini
                ({{ $todayStr }}) di list <strong>{{ $taskList->name }}</strong>
                @if ($space)
                  &mdash; {{ $space->name }}
                @endif.
              </p>

              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="border-top:1px solid #eeeeee;"></td>
                </tr>
              </table>

              @foreach ($pendingTasks as $dt)
                @php $log = $dt->logs->first(); @endphp
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                  style="margin-bottom:10px;border:1px solid #f3f3f3;border-radius:4px;background:#fafafa;">
                  <tr>
                    <td style="padding:10px 14px;">
                      <p style="margin:0;font-size:14px;font-weight:600;color:#111111;">{{ $dt->title }}</p>
                      @if ($dt->description)
                        <p style="margin:4px 0 0;font-size:12px;color:#777777;">{{ $dt->description }}</p>
                      @endif
                      @if ($log && $log->reason)
                        <p
                          style="margin:6px 0 0;font-size:12px;color:#b45309;border-left:3px solid #fbbf24;padding-left:8px;">
                          Alasan: {{ $log->reason }}
                        </p>
                      @endif
                    </td>
                  </tr>
                </table>
              @endforeach

              <table cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;">
                <tr>
                  <td style="border-radius:4px;background:#4f46e5;">
                    <a href="{{ $listUrl }}" target="_blank"
                      style="display:inline-block;padding:10px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">
                      Buka List
                    </a>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <tr>
            <td style="padding:20px 0 0;font-size:12px;color:#aaaaaa;text-align:center;line-height:1.7;">
              Email ini dikirim otomatis karena kamu adalah anggota list tersebut.<br>
              {{ config('app.name') }}
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>

</html>
