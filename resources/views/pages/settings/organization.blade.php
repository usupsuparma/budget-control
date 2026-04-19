<div id="layout-wrapper">
    <div class="card-body">
        <div class="org-tree">
            <style>
                .org-tree * {
                    box-sizing: border-box;
                }

                /* ── Tree container ── */
                .org-tree {
                    overflow-x: auto;
                    padding: 20px 8px;
                }

                /* ── Root level list ── */
                .org-tree>ul {
                    display: flex;
                    justify-content: center;
                    flex-wrap: wrap;
                    gap: 32px;
                    padding: 0;
                    margin: 0;
                }

                /* ── All list items ── */
                .org-tree ul {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                /* ── Children list (nested) ── */
                .org-tree ul.children {
                    display: flex;
                    justify-content: center;
                    flex-wrap: wrap;
                    gap: 16px;
                    padding-top: 24px;
                    position: relative;
                    margin-top: 0;
                }

                /* Horizontal line spanning children */
                .org-tree ul.children::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 10%;
                    right: 10%;
                    height: 2px;
                    background: rgba(148, 163, 184, 0.35);
                }

                /* Vertical connector from horizontal bar down to each node */
                .org-tree ul.children>li {
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }

                .org-tree ul.children>li::before {
                    content: '';
                    position: absolute;
                    top: -24px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 2px;
                    height: 24px;
                    background: rgba(148, 163, 184, 0.35);
                }

                /* Vertical connector from parent node down to horizontal line */
                .org-tree li.has-children>.org-node-wrap::after {
                    content: '';
                    display: block;
                    width: 2px;
                    height: 24px;
                    background: rgba(148, 163, 184, 0.35);
                    margin: 0 auto;
                }

                /* ── Node card ── */
                .org-node {
                    display: inline-flex;
                    flex-direction: column;
                    align-items: center;
                    padding: 10px 16px;
                    border-radius: 10px;
                    border: 1px solid rgba(148, 163, 184, 0.2);
                    background: #fff;
                    min-width: 150px;
                    max-width: 200px;
                    box-shadow: 0 2px 8px rgba(2, 6, 23, 0.07);
                    transition: transform 0.15s ease, box-shadow 0.15s ease;
                }

                .org-node:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 16px rgba(2, 6, 23, 0.12);
                }

                .org-node[data-toggle] {
                    cursor: pointer;
                }

                .org-node .node-title {
                    font-weight: 700;
                    font-size: 0.88rem;
                    color: #0f172a;
                    text-align: center;
                    word-break: break-word;
                }

                .org-node .node-meta {
                    font-size: 0.72rem;
                    color: #64748b;
                    margin-top: 3px;
                    text-align: center;
                }

                /* Level colour accents */
                .org-node.level-director {
                    border-top: 3px solid #6366f1;
                }

                .org-node.level-division {
                    border-top: 3px solid #0ea5e9;
                }

                .org-node.level-department {
                    border-top: 3px solid #10b981;
                }

                .org-node.level-section {
                    border-top: 3px solid #f59e0b;
                }

                /* Collapsed indicator */
                .org-node .toggle-icon {
                    margin-top: 5px;
                    font-size: 0.68rem;
                    color: #94a3b8;
                }

                /* Responsive */
                @media (max-width: 992px) {

                    .org-tree>ul,
                    .org-tree ul.children {
                        flex-direction: column;
                        align-items: center;
                    }

                    .org-tree ul.children::before,
                    .org-tree ul.children>li::before,
                    .org-tree li.has-children>.org-node-wrap::after {
                        display: none;
                    }
                }
            </style>

            {{-- ── ORGANIZATION TREE ── --}}
            <ul>
                @forelse($directors as $director)
                <li class="{{ $director->divisions->isNotEmpty() ? 'has-children' : '' }}">
                    <div class="org-node-wrap">
                        <div class="org-node level-director {{ $director->divisions->isNotEmpty() ? '' : '' }}"
                            {{ $director->divisions->isNotEmpty() ? 'data-toggle' : '' }}>
                            <div class="node-title">{{ $director->name }}</div>
                            <div class="node-meta">{{ $director->code ? $director->code . ' · ' : '' }}{{ $director->status }}</div>
                            @if(!empty($director->head_employee_name))
                            <div class="node-meta text-secondary">
                                {{ $director->head_employee_name }}@if(!empty($director->head_job_position)) · {{ $director->head_job_position }}@endif
                            </div>
                            @endif
                            @if($director->divisions->isNotEmpty())
                            <div class="toggle-icon">▾ {{ $director->divisions->count() }} divisions</div>
                            @endif
                        </div>
                    </div>

                    {{-- Level 2: Divisions --}}
                    @if($director->divisions->isNotEmpty())
                    <ul class="children">
                        @foreach($director->divisions as $division)
                        <li class="{{ $division->departments->isNotEmpty() ? 'has-children' : '' }}">
                            <div class="org-node-wrap">
                                <div class="org-node level-division"
                                    {{ $division->departments->isNotEmpty() ? 'data-toggle' : '' }}>
                                    <div class="node-title">{{ $division->name }}</div>
                                    <div class="node-meta">{{ $division->code ?? '' }}{{ $division->code ? ' · ' : '' }}{{ $division->status }}</div>
                                    @if(!empty($division->head_employee_name))
                                    <div class="node-meta text-secondary">
                                        {{ $division->head_employee_name }}@if(!empty($division->head_job_position)) · {{ $division->head_job_position }}@endif
                                    </div>
                                    @endif
                                    @if($division->departments->isNotEmpty())
                                    <div class="toggle-icon">▾ {{ $division->departments->count() }} depts</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Level 3: Departments --}}
                            @if($division->departments->isNotEmpty())
                            <ul class="children">
                                @foreach($division->departments as $department)
                                <li class="{{ $department->sections->isNotEmpty() ? 'has-children' : '' }}">
                                    <div class="org-node-wrap">
                                        <div class="org-node level-department"
                                            {{ $department->sections->isNotEmpty() ? 'data-toggle' : '' }}>
                                            <div class="node-title">{{ $department->name }}</div>
                                            <div class="node-meta">{{ $department->code ?? '' }}{{ $department->code ? ' · ' : '' }}{{ $department->status }}</div>
                                            @if(!empty($department->head_employee_name))
                                            <div class="node-meta text-secondary">
                                                {{ $department->head_employee_name }}@if(!empty($department->head_job_position)) · {{ $department->head_job_position }}@endif
                                            </div>
                                            @endif
                                            @if($department->sections->isNotEmpty())
                                            <div class="toggle-icon">▾ {{ $department->sections->count() }} sections</div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Level 4: Sections --}}
                                    @if($department->sections->isNotEmpty())
                                    <ul class="children">
                                        @foreach($department->sections as $section)
                                        <li>
                                            <div class="org-node level-section">
                                                <div class="node-title">{{ $section->name }}</div>
                                                <div class="node-meta">{{ $section->code ?? '' }}{{ ($section->code && $section->status) ? ' · ' : '' }}{{ $section->status ?? '' }}</div>
                                                @if(!empty($section->head_employee_name))
                                                <div class="node-meta text-secondary">
                                                    {{ $section->head_employee_name }}@if(!empty($section->head_job_position)) · {{ $section->head_job_position }}@endif
                                                </div>
                                                @endif
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @empty
                <li>
                    <div class="org-node level-director">
                        <div class="node-title">No directors found</div>
                        <div class="node-meta">Please add an active director to the system.</div>
                    </div>
                </li>
                @endforelse
            </ul>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // All children lists start visible
                    document.querySelectorAll('.org-tree ul.children').forEach(function(el) {
                        el.style.display = 'flex';
                    });

                    // Toggle collapse on click
                    document.querySelectorAll('.org-node[data-toggle]').forEach(function(node) {
                        node.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var parentLi = node.closest('li');
                            var childUl = parentLi ? parentLi.querySelector(':scope > ul.children') : null;
                            if (!childUl) return;

                            var isHidden = childUl.style.display === 'none';
                            childUl.style.display = isHidden ? 'flex' : 'none';

                            // Toggle icon text
                            var icon = node.querySelector('.toggle-icon');
                            if (icon) {
                                icon.textContent = icon.textContent.replace(isHidden ? '▸' : '▾', isHidden ? '▾' : '▸');
                            }
                        });
                    });
                });
            </script>
        </div>
    </div>
</div>