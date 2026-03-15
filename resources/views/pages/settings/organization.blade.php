<div id="layout-wrapper">
    <div class="card-body">
        <div class="org-tree">
            <style>
                .org-tree * {
                    box-sizing: border-box;
                }

                .org-tree ul {
                    padding-top: 20px;
                    position: relative;
                    transition: all 0.5s;
                    display: flex;
                    justify-content: center;
                    gap: 16px;
                    flex-wrap: nowrap;
                }

                .org-tree li {
                    list-style-type: none;
                    text-align: center;
                    position: relative;
                    padding: 0 5px;
                }

                .org-tree ul::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 10%;
                    right: 10%;
                    height: 1px;
                    background: rgba(148, 163, 184, 0.4);
                }

                .org-tree li::before {
                    content: '';
                    position: absolute;
                    top: -20px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 2px;
                    height: 20px;
                    background: rgba(148, 163, 184, 0.4);
                }

                .org-node {
                    display: inline-block;
                    padding: 10px 14px;
                    border-radius: 8px;
                    border: 1px solid rgba(148, 163, 184, 0.15);
                    background: #fff;
                    min-width: 160px;
                    cursor: default;
                    box-shadow: 0 2px 6px rgba(2, 6, 23, 0.06);
                }

                .org-node .title {
                    font-weight: 700;
                    font-size: 0.95rem;
                    color: #0f172a;
                }

                .org-node .meta {
                    font-size: 0.75rem;
                    color: #64748b;
                    margin-top: 4px;
                }

                .org-node .badge {
                    display: inline-block;
                    margin-top: 6px;
                    padding: 2px 8px;
                    font-size: 11px;
                    border-radius: 999px;
                    background: rgba(34, 197, 94, 0.08);
                    color: #059669;
                }

                .org-tree .children {
                    margin-top: 8px;
                    display: flex;
                    gap: 24px;
                    justify-content: center;
                }

                .org-tree .children ul {
                    display: flex;
                    gap: 24px;
                    padding-top: 12px;
                }

                .org-node[data-toggle] {
                    cursor: pointer;
                }

                .org-node:hover {
                    transform: translateY(-3px);
                    transition: transform 0.15s ease;
                }

                @media (max-width: 992px) {
                    .org-tree ul {
                        flex-direction: column;
                        align-items: center;
                    }

                    .org-tree ul::before {
                        display: none;
                    }

                    .org-tree li::before {
                        display: none;
                    }

                    .org-tree .children {
                        flex-direction: column;
                        gap: 12px;
                    }
                }
            </style>

            <ul>
                @forelse($directors as $director)
                <li>
                    <div class="org-node" data-toggle>
                        <div class="title">{{ $director->name }}</div>
                        <div class="meta">{{ $director->code ? $director->code . ' · ' : '' }}{{ $director->status }}</div>
                        @if(!empty($director->structure_id))
                        <div class="badge">Structure: {{ $director->structure_id }}</div>
                        @endif
                    </div>

                    childUl.style.display = (childUl.style.display === 'none' || childUl.style.display === '') ? 'flex' : 'none';
                    @if($director->divisions->isEmpty())
                <li>
                    <div class="org-node">
                        <div class="meta">No divisions</div>
                    </div>
                </li>
                @else
                @foreach($director->divisions as $division)
                <li>
                    <div class="org-node" data-toggle>
                        <div class="title">{{ $division->name }}</div>
                        <div class="meta">{{ $division->code ?? '' }} · {{ $division->status }}</div>
                    </div>

                    <ul class="children">
                        @if($division->departments->isEmpty())
                        <li>
                            <div class="org-node">
                                <div class="meta">No departments</div>
                            </div>
                        </li>
                        @else
                        @foreach($division->departments as $department)
                        <li>
                            <div class="org-node" data-toggle>
                                <div class="title">{{ $department->name }}</div>
                                <div class="meta">{{ $department->code ?? '' }} · {{ $department->status }}</div>
                            </div>

                            <ul class="children">
                                @if($department->sections->isEmpty())
                                <li>
                                    <div class="org-node">
                                        <div class="meta">No sections</div>
                                    </div>
                                </li>
                                @else
                                @foreach($department->sections as $section)
                                <li>
                                    <div class="org-node">
                                        <div class="title">{{ $section->name }}</div>
                                        <div class="meta">{{ $section->code ?? '' }} · {{ $section->status ?? '' }}</div>
                                    </div>
                                </li>
                                @endforeach
                                @endif
                            </ul>
                        </li>
                        @endforeach
                        @endif
                    </ul>
                </li>
                @endforeach
                @endif
            </ul>
            </li>
            @empty
            <li>
                <div class="org-node">
                    <div class="title">No directors found</div>
                </div>
            </li>
            @endforelse
            </ul>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // initialize: ensure inner children are visible
                    document.querySelectorAll('.org-tree ul.children').forEach(function(el) {
                        el.style.display = 'flex';
                    });

                    document.querySelectorAll('.org-node[data-toggle]').forEach(function(node) {
                        node.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var parent = node.parentElement;
                            var childUl = parent.querySelector(':scope > ul.children');
                            if (!childUl) return;
                            childUl.style.display = (childUl.style.display === 'none') ? 'flex' : 'none';
                        });
                    });
                });
            </script>
        </div>
    </div>
</div>