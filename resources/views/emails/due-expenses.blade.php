<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kind->title() }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:8px; overflow:hidden;">

                    <tr>
                        <td style="padding:24px; border-bottom:1px solid #e5e7eb;">
                            <h1 style="margin:0; font-size:18px; color:#111827;">{{ $kind->title() }}</h1>
                            <p style="margin:8px 0 0; font-size:14px; color:#6b7280;">
                                Olá, {{ $user->name }}. {{ $kind->callToAction() }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach($expenses as $expense)
                                    <tr>
                                        <td style="padding:12px 0; border-bottom:1px solid #f3f4f6;">
                                            <div style="font-size:14px; color:#111827; font-weight:600;">
                                                {{ $expense->description }}
                                            </div>
                                            <div style="font-size:13px; color:#6b7280; margin-top:2px;">
                                                Vence em {{ $expense->due_date->format('d/m/Y') }}
                                                @if($expense->category)
                                                    · {{ $expense->category->name }}
                                                @endif
                                            </div>
                                        </td>
                                        <td align="right" style="padding:12px 0; border-bottom:1px solid #f3f4f6; white-space:nowrap;">
                                            <span style="font-size:14px; color:#111827; font-weight:600;">
                                                R$ {{ number_format($expense->amount, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size:14px; color:#6b7280;">Total</td>
                                    <td align="right" style="font-size:16px; color:#111827; font-weight:700;">
                                        R$ {{ number_format($total, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:20px;">
                                <a href="{{ route('expenses.index') }}"
                                   style="display:inline-block; background-color:#4f46e5; color:#ffffff; text-decoration:none; padding:10px 18px; border-radius:6px; font-size:13px; font-weight:600;">
                                    Ver minhas despesas
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px; background-color:#f9fafb; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; font-size:12px; color:#9ca3af;">
                                Você recebe este aviso porque ativou notificações no seu perfil.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
