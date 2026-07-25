<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>NorthPole Control Centre</title>

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
            width: min(1440px, calc(100% - 40px));
            margin: 0 auto;
            padding: 36px 0 70px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 34px;
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
            box-shadow:
                0 18px 55px rgba(0, 0, 0, 0.25);
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

        .environment {
            padding: 8px 12px;
            border: 1px solid rgba(160, 181, 207, 0.18);
            border-radius: 999px;
            background: rgba(13, 28, 46, 0.7);
            color: #aabbd0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.6fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel {
            border: 1px solid rgba(151, 177, 206, 0.14);
            border-radius: 24px;
            background: rgba(10, 24, 40, 0.82);
            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(18px);
        }

        .health-panel {
            position: relative;
            overflow: hidden;
            min-height: 280px;
            padding: 34px;
        }

        .health-panel::after {
            position: absolute;
            right: -60px;
            bottom: -90px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background:
                radial-gradient(
                    circle,
                    rgba(40, 207, 159, 0.22),
                    transparent 68%
                );
            content: "";
        }

        .eyebrow {
            margin: 0 0 18px;
            color: #7f95ae;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .health-status {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .health-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #2ed6a1;
            box-shadow:
                0 0 0 8px rgba(46, 214, 161, 0.1),
                0 0 32px rgba(46, 214, 161, 0.7);
        }

        .health-status h2 {
            margin: 0;
            font-size: clamp(38px, 6vw, 70px);
            line-height: 1;
            letter-spacing: -0.06em;
        }

        .health-copy {
            max-width: 680px;
            margin: 0;
            color: #9cb0c6;
            font-size: 16px;
            line-height: 1.7;
        }

        .version-grid {
            display: grid;
            grid-template-columns: repeat(3, max-content);
            gap: 30px;
            margin-top: 34px;
        }

        .version-grid span {
            display: block;
            margin-bottom: 6px;
            color: #7188a2;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .version-grid strong {
            color: #dbe8f5;
            font-size: 14px;
        }

        .module-summary {
            display: grid;
            gap: 1px;
            overflow: hidden;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 92px;
            padding: 20px 26px;
            border-bottom: 1px solid rgba(151, 177, 206, 0.1);
        }

        .summary-row:last-child {
            border-bottom: 0;
        }

        .summary-row span {
            color: #91a5bc;
            font-size: 14px;
        }

        .summary-row strong {
            font-size: 31px;
            letter-spacing: -0.05em;
        }

        .section {
            margin-top: 20px;
            padding: 28px;
        }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
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

        .metrics {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .metric {
            min-height: 118px;
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.66);
        }

        .metric span {
            display: block;
            min-height: 34px;
            color: #849ab2;
            font-size: 12px;
            line-height: 1.4;
        }

        .metric strong {
            display: block;
            margin-top: 13px;
            font-size: 29px;
            letter-spacing: -0.05em;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .module-card {
            position: relative;
            overflow: hidden;
            padding: 22px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 20px;
            background:
                linear-gradient(
                    145deg,
                    rgba(14, 35, 57, 0.96),
                    rgba(9, 25, 43, 0.92)
                );
        }

        .module-card::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #31d7a2;
            content: "";
        }

        .module-card.disabled::before {
            background: #f2a84b;
        }

        .module-top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }

        .module-card h3 {
            margin: 0;
            font-size: 18px;
        }

        .module-slug {
            margin: 5px 0 0;
            color: #768ca6;
            font-size: 12px;
        }

        .badge {
            height: fit-content;
            padding: 6px 9px;
            border: 1px solid rgba(49, 215, 162, 0.25);
            border-radius: 999px;
            background: rgba(49, 215, 162, 0.1);
            color: #67e3bd;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .disabled .badge {
            border-color: rgba(242, 168, 75, 0.26);
            background: rgba(242, 168, 75, 0.09);
            color: #ffc779;
        }

        .module-description {
            min-height: 42px;
            margin: 18px 0;
            color: #90a5bc;
            font-size: 13px;
            line-height: 1.6;
        }

        .module-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .module-meta span {
            padding: 7px 9px;
            border-radius: 10px;
            background: rgba(129, 160, 195, 0.08);
            color: #9db0c5;
            font-size: 11px;
        }

        .empty {
            padding: 30px;
            border: 1px dashed rgba(154, 184, 218, 0.2);
            border-radius: 18px;
            color: #90a5bc;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 26px;
            color: #687e98;
            font-size: 12px;
        }

        @media (max-width: 1080px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .shell {
                width: min(100% - 24px, 1440px);
                padding-top: 20px;
            }

            .topbar,
            .section-header,
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .health-panel,
            .section {
                padding: 22px;
            }

            .version-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .metrics,
            .modules {
                grid-template-columns: 1fr;
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
                    <h1>NorthPole Control Centre</h1>
                    <p>Modular platform runtime administration</p>
                </div>
            </div>

            <div class="environment">
                {{ $environment }}
            </div>
        </header>

        <section class="hero">
            <article class="panel health-panel">
                <p class="eyebrow">Runtime health</p>

                <div class="health-status">
                    <span
                        class="health-dot"
                        aria-hidden="true"
                    ></span>

                    <h2>{{ $health }}</h2>
                </div>

                <p class="health-copy">
                    The NorthPole runtime is online and its discovered
                    modules and registries are available for inspection.
                </p>

                <div class="version-grid">
                    <div>
                        <span>Environment</span>
                        <strong>{{ $environment }}</strong>
                    </div>

                    <div>
                        <span>Laravel</span>
                        <strong>{{ $laravelVersion }}</strong>
                    </div>

                    <div>
                        <span>PHP</span>
                        <strong>{{ $phpVersion }}</strong>
                    </div>
                </div>
            </article>

            <aside class="panel module-summary">
                <div class="summary-row">
                    <span>Discovered modules</span>
                    <strong>
                        {{ $moduleSummary['discovered'] }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Enabled modules</span>
                    <strong>
                        {{ $moduleSummary['enabled'] }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Disabled modules</span>
                    <strong>
                        {{ $moduleSummary['disabled'] }}
                    </strong>
                </div>
            </aside>
        </section>

        <section class="panel section">
            <div class="section-header">
                <div>
                    <h2>Runtime registries</h2>
                    <p>
                        Live contributions loaded from enabled modules.
                    </p>
                </div>
            </div>

            <div class="metrics">
                @foreach ($metrics as $metric)
                    <article class="metric">
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="panel section">
            <div class="section-header">
                <div>
                    <h2>Discovered modules</h2>
                    <p>
                        Modules currently visible to the NorthPole runtime.
                    </p>
                </div>
            </div>

            @if ($modules === [])
                <div class="empty">
                    No NorthPole modules were discovered.
                </div>
            @else
                <div class="modules">
                    @foreach ($modules as $module)
                        <article
                            class="module-card
                                {{ $module['enabled'] ? '' : 'disabled' }}"
                        >
                            <div class="module-top">
                                <div>
                                    <h3>{{ $module['name'] }}</h3>

                                    <p class="module-slug">
                                        {{ $module['slug'] }}
                                        ·
                                        v{{ $module['version'] }}
                                    </p>
                                </div>

                                <span class="badge">
                                    {{ $module['enabled']
                                        ? 'Enabled'
                                        : 'Disabled' }}
                                </span>
                            </div>

                            <p class="module-description">
                                {{ $module['description']
                                    ?? 'No module description provided.' }}
                            </p>

                            <div class="module-meta">
                                <span>
                                    {{ $module['dependencies'] }}
                                    dependencies
                                </span>

                                <span>
                                    {{ $module['commands'] }}
                                    commands
                                </span>

                                <span>
                                    {{ $module['queries'] }}
                                    queries
                                </span>

                                <span>
                                    {{ $module['permissions'] }}
                                    permissions
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>
            <span>
                Live data generated from runtime registries
            </span>
        </footer>
    </main>
</body>
</html>