<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>NorthPole Runtime Diagnostics</title>

    <style>
        :root {
            color-scheme: dark;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: #07111f;
            color: #e8eef7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(
                    circle at top left,
                    rgba(28, 108, 137, 0.28),
                    transparent 34rem
                ),
                radial-gradient(
                    circle at top right,
                    rgba(69, 52, 141, 0.24),
                    transparent 30rem
                ),
                #07111f;
        }

        a {
            color: inherit;
        }

        .shell {
            width: min(1380px, calc(100% - 40px));
            margin: 0 auto;
            padding: 36px 0 70px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            border: 1px solid rgba(119, 219, 255, 0.4);
            border-radius: 16px;
            background:
                linear-gradient(
                    145deg,
                    rgba(62, 199, 232, 0.22),
                    rgba(115, 91, 255, 0.18)
                );
            font-size: 24px;
        }

        .brand h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        .brand p {
            margin: 4px 0 0;
            color: #92a5bc;
            font-size: 13px;
        }

        .back-link {
            padding: 9px 14px;
            border: 1px solid rgba(119, 219, 255, 0.25);
            border-radius: 999px;
            background: rgba(13, 28, 46, 0.72);
            color: #b8cde2;
            text-decoration: none;
            font-size: 12px;
        }

        .panel {
            border: 1px solid rgba(151, 177, 206, 0.14);
            border-radius: 24px;
            background: rgba(10, 24, 40, 0.82);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(18px);
        }

        .hero {
            display: grid;
            grid-template-columns:
                minmax(0, 1.35fr)
                minmax(320px, 0.65fr);
            gap: 20px;
        }

        .hero-copy {
            padding: 34px;
        }

        .eyebrow {
            margin: 0 0 15px;
            color: #7f95ae;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .hero-copy h2 {
            margin: 0;
            font-size: clamp(38px, 6vw, 66px);
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .hero-copy p {
            max-width: 700px;
            margin: 20px 0 0;
            color: #9cb0c6;
            font-size: 15px;
            line-height: 1.7;
        }

        .status-healthy {
            color: #5ce2b7;
        }

        .status-degraded,
        .status-unhealthy {
            color: #ffb760;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            overflow: hidden;
        }

        .summary-item {
            min-height: 120px;
            padding: 24px;
            border-right: 1px solid rgba(151, 177, 206, 0.1);
            border-bottom: 1px solid rgba(151, 177, 206, 0.1);
        }

        .summary-item:nth-child(even) {
            border-right: 0;
        }

        .summary-item:nth-last-child(-n + 2) {
            border-bottom: 0;
        }

        .summary-item span {
            display: block;
            color: #849ab2;
            font-size: 12px;
        }

        .summary-item strong {
            display: block;
            margin-top: 14px;
            font-size: 31px;
            letter-spacing: -0.05em;
        }

        .section {
            margin-top: 20px;
            padding: 28px;
        }

        .section-header {
            margin-bottom: 22px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.03em;
        }

        .section-header p {
            margin: 7px 0 0;
            color: #7f95ae;
            font-size: 13px;
        }

        .registries {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .registry-link {
            display: block;
            border-radius: 18px;
            text-decoration: none;
        }

        .registry-card {
            min-height: 116px;
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.66);
            transition:
                border-color 160ms ease,
                transform 160ms ease,
                box-shadow 160ms ease;
        }

        .registry-link:hover .registry-card {
            border-color: rgba(119, 219, 255, 0.4);
            box-shadow:
                0 20px 42px rgba(0, 0, 0, 0.22);
            transform: translateY(-2px);
        }

        .registry-card span {
            display: block;
            color: #849ab2;
            font-size: 12px;
        }

        .registry-card strong {
            display: block;
            margin-top: 16px;
            font-size: 29px;
            letter-spacing: -0.05em;
        }

        .issues {
            display: grid;
            gap: 12px;
        }

        .issue {
            padding: 18px;
            border: 1px solid rgba(239, 106, 117, 0.23);
            border-radius: 16px;
            background: rgba(239, 106, 117, 0.07);
        }

        .issue-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }

        .issue strong {
            color: #ffadb4;
            font-size: 14px;
        }

        .issue span {
            color: #8197af;
            font-size: 12px;
        }

        .issue p {
            margin: 10px 0 0;
            color: #a9b9ca;
            font-size: 13px;
            line-height: 1.6;
        }

        .clear {
            padding: 28px;
            border: 1px solid rgba(49, 215, 162, 0.22);
            border-radius: 18px;
            background: rgba(49, 215, 162, 0.07);
        }

        .clear strong {
            display: block;
            color: #67e3bd;
            font-size: 16px;
        }

        .clear p {
            margin: 8px 0 0;
            color: #91a9bd;
            font-size: 13px;
            line-height: 1.6;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 26px;
            color: #687e98;
            font-size: 12px;
        }

        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .registries {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .shell {
                width: min(100% - 24px, 1380px);
                padding-top: 20px;
            }

            .topbar,
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero-copy,
            .section {
                padding: 22px;
            }

            .summary,
            .registries {
                grid-template-columns: 1fr;
            }

            .summary-item,
            .summary-item:nth-child(even) {
                border-right: 0;
                border-bottom:
                    1px solid rgba(151, 177, 206, 0.1);
            }

            .summary-item:last-child {
                border-bottom: 0;
            }
        }
    </style>
</head>

<body>
    <main class="shell">
        <header class="topbar">
            <div class="brand">
                <div
                    class="brand-mark"
                    aria-hidden="true"
                >
                    ❄
                </div>

                <div>
                    <h1>Runtime Diagnostics</h1>
                    <p>
                        NorthPole runtime inspection and operational signals
                    </p>
                </div>
            </div>

            <a
                class="back-link"
                href="{{ route('control-centre') }}"
            >
                Back to Control Centre
            </a>
        </header>

        <section class="hero">
            <article class="panel hero-copy">
                <p class="eyebrow">
                    Runtime diagnostic status
                </p>

                <h2
                    class="status-{{ $summary['status'] }}"
                >
                    {{ strtoupper($summary['status']) }}
                </h2>

                <p>
                    This report inspects the current runtime health,
                    discovered modules, module checks, boot pipeline and
                    registered module contributions.
                </p>
            </article>

            <aside class="panel summary">
                <div class="summary-item">
                    <span>Runtime score</span>
                    <strong>{{ $summary['score'] }}%</strong>
                </div>

                <div class="summary-item">
                    <span>Boot stages</span>
                    <strong>{{ $summary['bootStages'] }}</strong>
                </div>

                <div class="summary-item">
                    <span>Healthy modules</span>
                    <strong>
                        {{ $summary['healthyModules'] }}
                        /
                        {{ $summary['modules'] }}
                    </strong>
                </div>

                <div class="summary-item">
                    <span>Detected issues</span>
                    <strong>{{ $summary['issues'] }}</strong>
                </div>
            </aside>
        </section>

        <section class="panel section">
            <div class="section-header">
                <h2>Registry statistics</h2>

                <p>
                    Current contributions loaded into the runtime.
                </p>
            </div>

            <div class="registries">
                @foreach ($registryCounts as $registry)
                    @if (isset($registry['registry']))
                        <a
                            class="registry-link"
                            href="{{ route(
                                'control-centre.runtime.registries.show',
                                [
                                    'registry' =>
                                        $registry['registry'],
                                ],
                            ) }}"
                        >
                            <article class="registry-card">
                                <span>{{ $registry['label'] }}</span>
                                <strong>{{ $registry['value'] }}</strong>
                            </article>
                        </a>
                    @else
                        <article class="registry-card">
                            <span>{{ $registry['label'] }}</span>
                            <strong>{{ $registry['value'] }}</strong>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="panel section">
            <div class="section-header">
                <h2>Module diagnostic issues</h2>

                <p>
                    Failed checks reported by discovered runtime modules.
                </p>
            </div>

            @if ($moduleIssues === [])
                <div class="clear">
                    <strong>No diagnostic issues detected</strong>

                    <p>
                        Every reported module health check currently passes.
                    </p>
                </div>
            @else
                <div class="issues">
                    @foreach ($moduleIssues as $issue)
                        <article class="issue">
                            <div class="issue-header">
                                <strong>{{ $issue['check'] }}</strong>
                                <span>{{ $issue['module'] }}</span>
                            </div>

                            <p>{{ $issue['message'] }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>
            <span>Live runtime diagnostic report</span>
        </footer>
    </main>
</body>
</html>