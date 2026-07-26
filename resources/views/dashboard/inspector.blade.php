<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        {{ $inspection['reference']['key'] }}
        | NorthPole Control Centre
    </title>

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
            text-decoration: none;
        }

        code,
        pre {
            font-family:
                "Cascadia Code",
                "SFMono-Regular",
                Consolas,
                monospace;
        }

        code {
            overflow-wrap: anywhere;
            color: #b9d9f4;
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
            font-size: 18px;
            font-weight: 700;
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

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .back-link {
            padding: 10px 14px;
            border: 1px solid rgba(151, 177, 206, 0.18);
            border-radius: 12px;
            background: rgba(13, 28, 46, 0.72);
            color: #aabbd0;
            font-size: 13px;
            transition:
                border-color 160ms ease,
                transform 160ms ease;
        }

        .back-link:hover {
            border-color: rgba(119, 219, 255, 0.45);
            transform: translateY(-1px);
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
            position: relative;
            overflow: hidden;
            padding: 34px;
        }

        .hero::after {
            position: absolute;
            right: -70px;
            bottom: -100px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background:
                radial-gradient(
                    circle,
                    rgba(62, 199, 232, 0.18),
                    transparent 68%
                );
            content: "";
        }

        .eyebrow {
            margin: 0 0 14px;
            color: #7f95ae;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }

        .hero h2 {
            position: relative;
            z-index: 1;
            max-width: 1050px;
            margin: 0;
            overflow-wrap: anywhere;
            font-size: clamp(32px, 5vw, 62px);
            line-height: 1.06;
            letter-spacing: -0.055em;
        }

        .reference {
            position: relative;
            z-index: 1;
            margin: 18px 0 0;
            color: #8fa5bc;
            font-size: 13px;
        }

        .reference code {
            color: #8fe1ff;
        }

        .identity-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .identity {
            min-height: 105px;
            padding: 18px;
            border: 1px solid rgba(154, 184, 218, 0.12);
            border-radius: 18px;
            background: rgba(11, 31, 51, 0.7);
        }

        .identity span {
            display: block;
            color: #849ab2;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .identity strong,
        .identity code {
            display: block;
            margin-top: 14px;
            font-size: 13px;
            line-height: 1.5;
        }

        .status-found {
            color: #67e3bd;
        }

        .content-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .section {
            min-width: 0;
            padding: 26px;
        }

        .section.wide {
            grid-column: 1 / -1;
        }

        .section h3 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.025em;
        }

        .section-copy {
            margin: 7px 0 20px;
            color: #7188a2;
            font-size: 12px;
            line-height: 1.6;
        }

        .definition-grid {
            display: grid;
            grid-template-columns: 130px minmax(0, 1fr);
            gap: 10px 16px;
            margin: 0;
        }

        .definition-grid dt {
            color: #7188a2;
            font-size: 11px;
        }

        .definition-grid dd {
            min-width: 0;
            margin: 0;
            color: #c9d8e8;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .metadata {
            margin: 0;
            padding: 18px;
            overflow-x: auto;
            border: 1px solid rgba(154, 184, 218, 0.11);
            border-radius: 15px;
            background: rgba(7, 17, 31, 0.6);
            color: #b9c9d9;
            font-size: 12px;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .items {
            display: grid;
            gap: 10px;
        }

        .item {
            padding: 15px;
            border: 1px solid rgba(154, 184, 218, 0.1);
            border-radius: 14px;
            background: rgba(11, 31, 51, 0.58);
            color: #a9bbce;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .warning {
            border-color: rgba(255, 183, 96, 0.25);
            background: rgba(255, 183, 96, 0.07);
            color: #ffc980;
        }

        .empty {
            padding: 24px;
            border: 1px dashed rgba(154, 184, 218, 0.18);
            border-radius: 15px;
            color: #7188a2;
            font-size: 12px;
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

        @media (max-width: 980px) {
            .identity-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .shell {
                width: min(100% - 24px, 1440px);
                padding-top: 20px;
            }

            .topbar,
            .footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero,
            .section {
                padding: 22px;
            }

            .identity-grid,
            .content-grid,
            .definition-grid {
                grid-template-columns: 1fr;
            }

            .section.wide {
                grid-column: auto;
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
                    <h1>NorthPole Control Centre</h1>
                    <p>Runtime registration inspector</p>
                </div>
            </div>

            <div class="actions">
                <a
                    class="back-link"
                    href="{{ route(
                        'control-centre.runtime.registries.show',
                        [
                            'registry' =>
                                $inspection['reference']['registry'],
                        ],
                    ) }}"
                >
                    Back to Registry
                </a>

                <a
                    class="back-link"
                    href="{{ route('control-centre') }}"
                >
                    Control Centre
                </a>
            </div>
        </header>

        <section class="panel hero">
            <p class="eyebrow">
                Runtime registration
            </p>

            <h2>
                {{ $inspection['reference']['key'] }}
            </h2>

            <p class="reference">
                Reference:
                <code>
                    {{
                        implode(
                            ':',
                            $inspection['reference'],
                        )
                    }}
                </code>
            </p>
        </section>

        <section class="identity-grid">
            <article class="identity">
                <span>Status</span>

                <strong class="status-found">
                    FOUND
                </strong>
            </article>

            <article class="identity">
                <span>Registry</span>

                <strong>
                    {{
                        str(
                            $inspection['reference']['registry'],
                        )
                            ->replace('-', ' ')
                            ->title()
                    }}
                </strong>
            </article>

            <article class="identity">
                <span>Module</span>

                <strong>
                    {{ $inspection['reference']['module'] }}
                </strong>
            </article>

            <article class="identity">
                <span>Resolved class</span>

                <code>
                    {{ $inspection['source']['class']
                        ?? 'No class resolved' }}
                </code>
            </article>
        </section>

        <div class="content-grid">
            <section class="panel section wide">
                <h3>Registration metadata</h3>

                <p class="section-copy">
                    The raw metadata returned by the runtime inspection
                    engine.
                </p>

                <pre class="metadata">{{ json_encode(
                    $inspection['metadata'],
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE,
                ) }}</pre>
            </section>

            <section class="panel section">
                <h3>Source information</h3>

                <p class="section-copy">
                    Resolved PHP class and physical source file.
                </p>

                <dl class="definition-grid">
                    <dt>Class</dt>

                    <dd>
                        <code>
                            {{ $inspection['source']['class']
                                ?? 'Not resolved' }}
                        </code>
                    </dd>

                    <dt>File</dt>

                    <dd>
                        <code>
                            {{ $inspection['source']['file']
                                ?? 'Not resolved' }}
                        </code>
                    </dd>
                </dl>
            </section>

            <section class="panel section">
                <h3>Reference</h3>

                <p class="section-copy">
                    Stable identifier used by the runtime inspection layer.
                </p>

                <dl class="definition-grid">
                    @foreach (
                        $inspection['reference']
                        as $field => $value
                    )
                        <dt>
                            {{ str($field)->title() }}
                        </dt>

                        <dd>
                            <code>{{ $value }}</code>
                        </dd>
                    @endforeach
                </dl>
            </section>

            <section class="panel section">
                <h3>Relationships</h3>

                <p class="section-copy">
                    Runtime registrations related to this entry.
                </p>

                @if ($inspection['relationships'] === [])
                    <div class="empty">
                        No relationships have been resolved yet.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $inspection['relationships']
                            as $relationship
                        )
                            <div class="item">
                                {{
                                    is_scalar($relationship)
                                        ? $relationship
                                        : json_encode(
                                            $relationship,
                                            JSON_UNESCAPED_SLASHES,
                                        )
                                }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section">
                <h3>Dependencies</h3>

                <p class="section-copy">
                    Runtime dependencies required by this registration.
                </p>

                @if ($inspection['dependencies'] === [])
                    <div class="empty">
                        No dependencies have been resolved yet.
                    </div>
                @else
                    <div class="items">
                        @foreach (
                            $inspection['dependencies']
                            as $dependency
                        )
                            <div class="item">
                                {{
                                    is_scalar($dependency)
                                        ? $dependency
                                        : json_encode(
                                            $dependency,
                                            JSON_UNESCAPED_SLASHES,
                                        )
                                }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="panel section wide">
                <h3>Warnings</h3>

                <p class="section-copy">
                    Inspection warnings and unresolved runtime concerns.
                </p>

                @if ($inspection['warnings'] === [])
                    <div class="empty">
                        No inspection warnings were reported.
                    </div>
                @else
                    <div class="items">
                        @foreach ($inspection['warnings'] as $warning)
                            <div class="item warning">
                                {{
                                    is_scalar($warning)
                                        ? $warning
                                        : json_encode(
                                            $warning,
                                            JSON_UNESCAPED_SLASHES,
                                        )
                                }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <footer class="footer">
            <span>NorthPole Platform Runtime</span>

            <span>
                Live registration inspection data
            </span>
        </footer>
    </main>
</body>
</html>