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

        .validation-overview {
            display: grid;
            grid-template-columns:
                repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }

        .validation-stat {
            min-height: 112px;
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.66);
        }

        .validation-stat span {
            display: block;
            color: #849ab2;
            font-size: 12px;
        }

        .validation-stat strong {
            display: block;
            margin-top: 16px;
            font-size: 28px;
            letter-spacing: -0.05em;
        }

        .validation-stat.status-passed {
            border-color: rgba(49, 215, 162, 0.26);
            background: rgba(49, 215, 162, 0.07);
        }

        .validation-stat.status-passed strong {
            color: #67e3bd;
        }

        .validation-stat.status-failed {
            border-color: rgba(239, 106, 117, 0.28);
            background: rgba(239, 106, 117, 0.07);
        }

        .validation-stat.status-failed strong {
            color: #ffadb4;
        }

        .validation-issues {
            display: grid;
            gap: 12px;
        }

        .validation-issue {
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.14);
            border-radius: 16px;
            background: rgba(11, 31, 51, 0.58);
        }

        .validation-issue.severity-error {
            border-color: rgba(239, 106, 117, 0.28);
            background: rgba(239, 106, 117, 0.07);
        }

        .validation-issue.severity-warning {
            border-color: rgba(255, 183, 96, 0.28);
            background: rgba(255, 183, 96, 0.07);
        }

        .validation-issue.severity-info {
            border-color: rgba(119, 219, 255, 0.24);
            background: rgba(119, 219, 255, 0.06);
        }

        .validation-issue-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .validation-issue-title {
            min-width: 0;
        }

        .validation-issue-title strong {
            display: block;
            color: #dce8f5;
            font-size: 14px;
        }

        .validation-issue-title span {
            display: block;
            margin-top: 5px;
            color: #8197af;
            font-size: 12px;
        }

        .validation-severity {
            flex: 0 0 auto;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(151, 177, 206, 0.1);
            color: #9db1c8;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .severity-error .validation-severity {
            background: rgba(239, 106, 117, 0.14);
            color: #ffadb4;
        }

        .severity-warning .validation-severity {
            background: rgba(255, 183, 96, 0.14);
            color: #ffc980;
        }

        .severity-info .validation-severity {
            background: rgba(119, 219, 255, 0.12);
            color: #8fe1ff;
        }

        .validation-issue p {
            margin: 12px 0 0;
            color: #a9b9ca;
            font-size: 13px;
            line-height: 1.6;
        }

        .validation-context {
            display: grid;
            gap: 8px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid rgba(151, 177, 206, 0.1);
        }

        .validation-context-row {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr);
            gap: 14px;
            color: #91a5ba;
            font-size: 12px;
        }

        .validation-context-row dt {
            color: #6f879f;
        }

        .validation-context-row dd {
            min-width: 0;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .repair-overview {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }

        .repair-stat {
            min-height: 112px;
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.66);
        }

        .repair-stat span {
            display: block;
            color: #849ab2;
            font-size: 12px;
        }

        .repair-stat strong {
            display: block;
            margin-top: 16px;
            font-size: 28px;
            letter-spacing: -0.05em;
        }

        .repair-stat.status-clear {
            border-color: rgba(49, 215, 162, 0.26);
            background: rgba(49, 215, 162, 0.07);
        }

        .repair-stat.status-clear strong {
            color: #67e3bd;
        }

        .repair-stat.status-recommended {
            border-color: rgba(255, 183, 96, 0.28);
            background: rgba(255, 183, 96, 0.07);
        }

        .repair-stat.status-recommended strong {
            color: #ffc980;
        }

        .repair-recommendations {
            display: grid;
            gap: 14px;
        }

        .repair-recommendation {
            padding: 20px;
            border: 1px solid rgba(119, 219, 255, 0.2);
            border-radius: 18px;
            background: rgba(119, 219, 255, 0.05);
        }

        .repair-recommendation.severity-error {
            border-color: rgba(239, 106, 117, 0.28);
            background: rgba(239, 106, 117, 0.07);
        }

        .repair-recommendation.severity-warning {
            border-color: rgba(255, 183, 96, 0.28);
            background: rgba(255, 183, 96, 0.07);
        }

        .repair-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .repair-header strong {
            color: #dce8f5;
            font-size: 15px;
        }

        .repair-header span {
            flex: 0 0 auto;
            color: #8197af;
            font-size: 11px;
        }

        .repair-recommendation > p {
            margin: 12px 0 0;
            color: #a9b9ca;
            font-size: 13px;
            line-height: 1.6;
        }

        .repair-actions {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .repair-action {
            padding: 14px;
            border: 1px solid rgba(151, 177, 206, 0.12);
            border-radius: 14px;
            background: rgba(7, 17, 31, 0.42);
        }

        .repair-action strong {
            display: block;
            color: #9fdff4;
            font-size: 12px;
        }

        .repair-action span {
            display: block;
            margin-top: 4px;
            color: #7189a2;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .repair-action pre {
            margin: 10px 0 0;
            overflow-x: auto;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            color: #b9c9d9;
            font-family:
                "SFMono-Regular",
                Consolas,
                "Liberation Mono",
                monospace;
            font-size: 12px;
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

            .validation-overview {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .repair-overview {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
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
            .registries,
            .validation-overview,
            .repair-overview {
                grid-template-columns: 1fr;
            }

            .validation-context-row {
                grid-template-columns: 1fr;
                gap: 4px;
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
                    NP
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
                <h2>Runtime validation</h2>

                <p>
                    Structural validation of registered command and query
                    handlers.
                </p>
            </div>

            <div class="validation-overview">
                <article
                    class="validation-stat status-{{ $validationSummary['status'] }}"
                >
                    <span>Validation status</span>

                    <strong>
                        {{ strtoupper(
                            $validationSummary['status'],
                        ) }}
                    </strong>
                </article>

                <article class="validation-stat">
                    <span>Rules executed</span>
                    <strong>{{ $validationSummary['rules'] }}</strong>
                </article>

                <article class="validation-stat">
                    <span>Errors</span>
                    <strong>{{ $validationSummary['errors'] }}</strong>
                </article>

                <article class="validation-stat">
                    <span>Warnings</span>
                    <strong>{{ $validationSummary['warnings'] }}</strong>
                </article>

                <article class="validation-stat">
                    <span>Information</span>
                    <strong>
                        {{ $validationSummary['information'] }}
                    </strong>
                </article>
            </div>

            @if ($validationIssues === [])
                <div class="clear">
                    <strong>Runtime validation passed</strong>

                    <p>
                        All registered command and query handlers currently
                        satisfy the runtime validation rules.
                    </p>
                </div>
            @else
                <div class="validation-issues">
                    @foreach ($validationIssues as $issue)
                        <article
                            class="validation-issue severity-{{ $issue['severity'] }}"
                        >
                            <div class="validation-issue-header">
                                <div class="validation-issue-title">
                                    <strong>
                                        {{ $issue['message'] }}
                                    </strong>

                                    <span>
                                        {{ $issue['code'] }}

                                        @if ($issue['module'] !== null)
                                            · {{ $issue['module'] }}
                                        @endif
                                    </span>
                                </div>

                                <span class="validation-severity">
                                    {{ $issue['severity'] }}
                                </span>
                            </div>

                            @if ($issue['context'] !== [])
                                <dl class="validation-context">
                                    @foreach (
                                        $issue['context']
                                        as $key => $value
                                    )
                                        <div
                                            class="validation-context-row"
                                        >
                                            <dt>{{ $key }}</dt>

                                            <dd>
                                                @if (
                                                    is_array($value)
                                                    || is_object($value)
                                                )
                                                    {{ json_encode(
                                                        $value,
                                                        JSON_UNESCAPED_SLASHES,
                                                    ) }}
                                                @elseif (is_bool($value))
                                                    {{ $value
                                                        ? 'true'
                                                        : 'false' }}
                                                @elseif ($value === null)
                                                    null
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="panel section">
            <div class="section-header">
                <h2>Automatic repair recommendations</h2>

                <p>
                    Suggested actions generated from current runtime
                    validation issues.
                </p>
            </div>

            <div class="repair-overview">
                <article
                    class="repair-stat status-{{ $repairSummary['status'] }}"
                >
                    <span>Repair status</span>

                    <strong>
                        {{ strtoupper($repairSummary['status']) }}
                    </strong>
                </article>

                <article class="repair-stat">
                    <span>Recommendations</span>

                    <strong>
                        {{ $repairSummary['recommendations'] }}
                    </strong>
                </article>

                <article class="repair-stat">
                    <span>Repair providers</span>

                    <strong>
                        {{ $repairSummary['providers'] }}
                    </strong>
                </article>
            </div>

            @if ($repairRecommendations === [])
                <div class="clear">
                    <strong>No repairs currently required</strong>

                    <p>
                        The repair engine found no supported validation
                        issues requiring corrective action.
                    </p>
                </div>
            @else
                <div class="repair-recommendations">
                    @foreach (
                        $repairRecommendations
                        as $recommendation
                    )
                        <article
                            class="repair-recommendation severity-{{ $recommendation['severity'] }}"
                        >
                            <div class="repair-header">
                                <strong>
                                    {{ $recommendation['title'] }}
                                </strong>

                                <span>
                                    {{ $recommendation['code'] }}

                                    @if (
                                        $recommendation['module']
                                        !== null
                                    )
                                        · {{ $recommendation['module'] }}
                                    @endif
                                </span>
                            </div>

                            <p>
                                {{ $recommendation['description'] }}
                            </p>

                            @if (
                                $recommendation['actions']
                                !== []
                            )
                                <div class="repair-actions">
                                    @foreach (
                                        $recommendation['actions']
                                        as $action
                                    )
                                        <article class="repair-action">
                                            <strong>
                                                {{ $action['label'] }}
                                            </strong>

                                            <span>
                                                {{ $action['type'] }}
                                            </span>

                                            <pre>{{ $action['content'] }}</pre>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
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