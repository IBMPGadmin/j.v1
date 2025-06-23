@extends('layouts.user-layout')

@section('content')
<div class="container-fluid">
    <!-- Debug button for testing -->
    <button id="debug-btn" class="btn btn-sm btn-outline-secondary mb-2" onclick="runDebugChecks()">Run Debug Checks</button>
    <div id="debug-results" class="small bg-light p-2 mb-2" style="display:none;"></div>
    
    <!-- Test section lookup directly -->
    <div class="input-group mb-2" style="max-width: 500px;">
        <input type="text" id="test-section-input" class="form-control form-control-sm" placeholder="Test section number (e.g. 46)">
        <button class="btn btn-sm btn-primary" onclick="testSectionLookup()">Test Lookup</button>
    </div>
    <div id="test-section-result" class="small bg-light p-2 mb-2" style="display:none;"></div>
    
    <!-- Explanation box at the top of the content -->
    <div class="alert alert-info mb-3">
        <h5 class="alert-heading">Interactive Legal Reference System</h5>
        <p>This system allows you to click on legal references to view the referenced content:</p>
        <ul>
            <li><strong>Section references</strong> - Click on text like "section 2" to view that section</li>
            <li><strong>Subsection references</strong> - Click on text like "subsection (3)" to view that subsection</li>
            <li><strong>Paragraph references</strong> - Click on text like "paragraph (a)" to view that paragraph</li>
            <li><strong>Direct links</strong> - Click on the 🔗 icon next to any content to get a direct link to that specific item</li>
        </ul>
        <p>You can also create annotations on any section by using the "Add Note" button in the popup.</p>
    </div>
    
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8 col-md-7">
            <h2>Table: {{ $legalTable->table_name }}</h2>
            <h4>({{ $legalTable->act_name ?? '' }})</h4>
            @if(isset($client) && $client)
                <div class="alert alert-info mb-3">
                    <strong>Client:</strong> {{ $client->client_name ?? 'N/A' }}<br>
                    <strong>Email:</strong> {{ $client->client_email ?? 'N/A' }}<br>
                    <strong>Status:</strong> {{ $client->client_status ?? 'N/A' }}
                </div>
            @endif
            @if(empty($columns))
                <div class="alert alert-warning mt-4">No data found in this table.</div>
            @else
            <!-- Keyword Search Section -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Keyword Search</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.legalTables.view', $legalTable->id) }}" method="GET" class="mb-3">
                        @if(isset($client) && $client)
                            <input type="hidden" name="client_id" value="{{ $client->id }}">
                        @endif
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Content Display Area -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Legal Content</h5>
                </div>
                <div class="card-body" id="legal-content-area">

                    @php
                        // Group data by part, division, sub_division, section, etc.
                        $hierarchy = [];
                        foreach ($tableData as $row) {
                            $part = $row->part ?? 'General';
                            $division = $row->division ?? null;
                            $sub_division = $row->sub_division ?? null;
                            $section = $row->section ?? null;
                            $sub_section = $row->sub_section ?? null;
                            $paragraph = $row->paragraph ?? null;
                            $sub_paragraph = $row->sub_paragraph ?? null;
                            $hierarchy[$part][$division][$sub_division][$section][$sub_section][$paragraph][$sub_paragraph][] = $row;
                        }
                    @endphp
                    
                    <!-- Hierarchical Legal Structure -->
                    @foreach($hierarchy as $partNum => $divisions)
                        <div class="legal-section part-section mb-4">                            <h3 class="clickable-heading" data-section-type="part" data-section-id="{{ $partNum }}" data-table-id="{{ $legalTable->id }}">
                                Part {{ $partNum }}
                            </h3>
                            <div class="legal-content pl-4">
                                @foreach($divisions as $divisionNum => $subDivisions)
                                    @if($divisionNum)
                                        <div class="legal-section division-section mb-3">                                            <h4 class="clickable-heading" data-section-type="division" data-section-id="{{ $divisionNum }}" data-table-id="{{ $legalTable->id }}">
                                                Division {{ $divisionNum }}
                                            </h4>
                                            <div class="legal-content pl-4">
                                    @endif
                                    
                                    @foreach($subDivisions as $subDivNum => $sections)
                                        @if($subDivNum)
                                            <div class="legal-section subdivision-section mb-3">                                                <h5 class="clickable-heading" data-section-type="subdivision" data-section-id="{{ $subDivNum }}" data-table-id="{{ $legalTable->id }}">
                                                    Subdivision {{ $subDivNum }}
                                                </h5>
                                                <div class="legal-content pl-4">
                                        @endif
                                        
                                        @foreach($sections as $sectionNum => $subSections)
                                            @if($sectionNum)
                                                <div class="legal-section section-section mb-2">                                                    <h6 class="clickable-heading" data-section-type="section" data-section-id="{{ $sectionNum }}" data-table-id="{{ $legalTable->id }}">
                                                        Section {{ $sectionNum }}
                                                    </h6>
                                                    <div class="legal-content pl-4">
                                            @endif
                                            
                                            @foreach($subSections as $subSectionNum => $paragraphs)
                                                @if($subSectionNum)
                                                    <div class="legal-section subsection-section mb-2">                                                        <div class="clickable-heading" data-section-type="subsection" data-section-id="{{ $subSectionNum }}" data-table-id="{{ $legalTable->id }}">
                                                            <strong>Subsection {{ $subSectionNum }}</strong>
                                                        </div>
                                                        <div class="legal-content pl-4">
                                                @endif
                                                
                                                @foreach($paragraphs as $paraNum => $subParas)
                                                    @if($paraNum)
                                                        <div class="legal-section paragraph-section mb-2">                                                            <div class="clickable-heading" data-section-type="paragraph" data-section-id="{{ $paraNum }}" data-table-id="{{ $legalTable->id }}">
                                                                <strong>Paragraph {{ $paraNum }}</strong>
                                                            </div>
                                                            <div class="legal-content pl-4">
                                                    @endif
                                                    
                                                    @foreach($subParas as $subParaNum => $rows)
                                                        @if($subParaNum)
                                                            <div class="legal-section subparagraph-section mb-2">                                                                <div class="clickable-heading" data-section-type="subparagraph" data-section-id="{{ $subParaNum }}" data-table-id="{{ $legalTable->id }}">
                                                                    <strong>Sub-paragraph {{ $subParaNum }}</strong>
                                                                </div>
                                                                <div class="legal-content pl-4">
                                                        @endif
                                                        
                                                        @foreach($rows as $row)
                                                            <div class="legal-text mb-2" data-row-id="{{ $row->id ?? '' }}">
                                                                @if(!empty($row->title))<strong>{{ $row->title }}</strong><br>@endif
                                                                
                                                                @if(!empty($row->text_content))
                                                                    <span id="content-{{ $row->id ?? 'unknown' }}" class="legal-content-text">{!! $row->text_content !!}</span>
                                                                    <span class="direct-reference" data-ref-id="{{ $legalTable->id }}:{{ $row->id }}" title="Direct reference to this content">🔗</span>
                                                                    <script>
                                                                        document.addEventListener('DOMContentLoaded', function() {
                                                                            const contentElement = document.getElementById('content-{{ $row->id ?? 'unknown' }}');
                                                                            if (contentElement) {
                                                                                // Original text content
                                                                                const originalText = contentElement.innerHTML;
                                                                                
                                                                                // Process pattern 1: section X references
                                                                                let processedText = originalText.replace(
                                                                                    /\b(section|sections)\s+(\d+(?:\.\d+)?)\b/gi,
                                                                                    '<span class="ref" data-section-id="$2" data-table-id="{{ $legalTable->id }}">$1 $2</span>'
                                                                                );
                                                                                
                                                                                // Process pattern 2: paragraph references - now with better context handling
                                                                                processedText = processedText.replace(
                                                                                    /\b(paragraph|paragraphs)\s+\(([a-z\d\.]+)\)(?:\s+or\s+\(([a-z\d\.]+)\))?/gi,
                                                                                    function(match, type, firstRef, secondRef) {
                                                                                        // We need to get the context - if we have a section value for this row, use it
                                                                                        const section = "{{ $row->section ?? '' }}";
                                                                                        const subsection = "{{ $row->sub_section ?? '' }}";
                                                                                        
                                                                                        let sectionId;
                                                                                        if (section) {
                                                                                            sectionId = section;
                                                                                            if (subsection) {
                                                                                                sectionId += '(' + subsection + ')';
                                                                                            }
                                                                                            sectionId += '(' + firstRef + ')';
                                                                                        } else {
                                                                                            // If no section context available, just use the direct paragraph reference
                                                                                            sectionId = '(' + firstRef + ')';
                                                                                        }
                                                                                        
                                                                                        let result = '<span class="ref" data-section-id="' + sectionId + '" data-table-id="{{ $legalTable->id }}">'+
                                                                                            type + ' (' + firstRef + ')</span>';
                                                                                        
                                                                                        if (secondRef) {
                                                                                            let secondSectionId;
                                                                                            if (section) {
                                                                                                secondSectionId = section;
                                                                                                if (subsection) {
                                                                                                    secondSectionId += '(' + subsection + ')';
                                                                                                }
                                                                                                secondSectionId += '(' + secondRef + ')';
                                                                                            } else {
                                                                                                secondSectionId = '(' + secondRef + ')';
                                                                                            }
                                                                                            
                                                                                            result += ' or <span class="ref" data-section-id="' + secondSectionId + '" data-table-id="{{ $legalTable->id }}">'+
                                                                                                '(' + secondRef + ')</span>';
                                                                                        }
                                                                                        
                                                                                        return result;
                                                                                    }
                                                                                );
                                                                                
                                                                                // Process pattern 3: subsection references - with better context
                                                                                processedText = processedText.replace(
                                                                                    /\b(subsection|subsections)\s+\((\d+(?:\.\d+)?)\)(?:\s+or\s+\((\d+(?:\.\d+)?)\))?/gi,
                                                                                    function(match, type, firstRef, secondRef) {
                                                                                        // If we have a section value for this row, use it for context
                                                                                        const section = "{{ $row->section ?? '' }}";
                                                                                        
                                                                                        let sectionId;
                                                                                        if (section) {
                                                                                            sectionId = section + '(' + firstRef + ')';
                                                                                        } else {
                                                                                            // If no section context available, just use the direct subsection reference
                                                                                            sectionId = '(' + firstRef + ')';
                                                                                        }
                                                                                        
                                                                                        let result = '<span class="ref" data-section-id="' + sectionId + '" data-table-id="{{ $legalTable->id }}">'+
                                                                                            type + ' (' + firstRef + ')</span>';
                                                                                        
                                                                                        if (secondRef) {
                                                                                            let secondSectionId;
                                                                                            if (section) {
                                                                                                secondSectionId = section + '(' + secondRef + ')';
                                                                                            } else {
                                                                                                secondSectionId = '(' + secondRef + ')';
                                                                                            }
                                                                                            
                                                                                            result += ' or <span class="ref" data-section-id="' + secondSectionId + '" data-table-id="{{ $legalTable->id }}">'+
                                                                                                '(' + secondRef + ')</span>';
                                                                                        }
                                                                                        
                                                                                        return result;
                                                                                    }
                                                                                );
                                                                                
                                                                                // Process pattern 4: complex section references like 279.1(2)
                                                                                processedText = processedText.replace(
                                                                                    /\b(\d+(?:\.\d+)?(?:\([^)]+\)){1,4})\b(?!\s*\([a-z](?:\.\d+)?\))(?![^<>]*<\/span>)/g,
                                                                                    '<span class="ref" data-section-id="$1" data-table-id="{{ $legalTable->id }}">$1</span>'
                                                                                );
                                                                                
                                                                                // Process pattern 5: explicit section references
                                                                                processedText = processedText.replace(
                                                                                    /\b(section|subsection|paragraph)\s+(\d+(?:\.\d+)?)\((\d+(?:\.\d+)?)\)(?:\(([a-z\d\.]+)\))?/gi,
                                                                                    function(match, type, section, subsection, paragraph) {
                                                                                        let sectionId = section + '(' + subsection + ')';
                                                                                        if (paragraph) {
                                                                                            sectionId += '(' + paragraph + ')';
                                                                                        }
                                                                                        
                                                                                        return '<span class="ref" data-section-id="' + sectionId + '" data-table-id="{{ $legalTable->id }}">' + match + '</span>';
                                                                                    }
                                                                                );
                                                                                
                                                                                // Update the content with processed text
                                                                                contentElement.innerHTML = processedText;
                                                                            }
                                                                        });
                                                                    </script>
                                                                @endif
                                                                @if(!empty($row->footnote))<div class="footnote"><em>{!! $row->footnote !!}</em></div>@endif
                                                            </div>
                                                        @endforeach
                                                        
                                                        @if($subParaNum)
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                    
                                                    @if($paraNum)
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                                
                                                @if($subSectionNum)
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            
                                            @if($sectionNum)
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        @if($subDivNum)
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                    @if($divisionNum)
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
              <div class="d-flex justify-content-center mt-4">
                {!! $tableData->appends(request()->query())->links() !!}
            </div>
            
            <!-- Test button for modal -->
            <button type="button" class="btn btn-primary mt-3" id="test-modal-button">
                Test Modal
            </button>
            
            @endif
        </div>
        
        <!-- Right Side Container -->
        <div class="col-lg-4 col-md-5">
            <div class="sticky-top" style="top: 80px;">
                <!-- Section Content Viewer -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0" id="content-viewer-title">Section Content</h5>
                    </div>
                    <div class="card-body">
                        <div id="section-content-display" class="p-2">
                            <p class="text-muted">Click on any section heading to view its content here.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Droppable Area -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Notes & Annotations</h5>
                    </div>
                    <div class="card-body">
                        <div id="droppable-area" class="p-3 border border-dashed" style="min-height: 150px; background: #f8f9fa;">
                            <p class="text-muted text-center">Drag text here to make notes</p>
                        </div>
                        <textarea id="notes-area" class="form-control mt-3" rows="4" placeholder="Add your notes here..."></textarea>
                        <button class="btn btn-primary w-100 mt-3">Save Notes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Viewer Modal -->
    <div class="modal fade" id="contentViewerModal" tabindex="-1" aria-labelledby="contentViewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contentViewerModalLabel">Section Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-content-display">
                    <p>Modal content will load here</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Debug info and script for testing -->
    <div id="debug-output" class="card mt-3 mb-3" style="display: none;">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Debug Information</h5>
            <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="document.getElementById('debug-output').style.display='none'"></button>
        </div>
        <div class="card-body">
            <div id="debug-content" class="small font-monospace"></div>
        </div>
    </div>

    <script>
    // Debug function to check references on the page
    function runDebugChecks() {
        const debugOutput = document.getElementById('debug-output');
        const debugContent = document.getElementById('debug-content');
        
        if (!debugOutput || !debugContent) return;
        
        // Show debug panel
        debugOutput.style.display = 'block';
        
        // Clear previous output
        debugContent.innerHTML = '<div class="text-center">Running debug checks...</div>';
        
        setTimeout(() => {
            let output = '<h5>Reference Elements</h5>';
            
            // Count and display reference elements
            const refElements = document.querySelectorAll('.ref');
            output += `<p>Found ${refElements.length} reference elements on the page.</p>`;
            
            // Sample a few references to check their data attributes
            const sampleSize = Math.min(5, refElements.length);
            if (sampleSize > 0) {
                output += '<h6>Sample References:</h6><ul>';
                
                for (let i = 0; i < sampleSize; i++) {
                    const ref = refElements[i];
                    const sectionId = ref.getAttribute('data-section-id');
                    const tableId = ref.getAttribute('data-table-id') || ref.getAttribute('data-category-id');
                    
                    output += `<li>
                        <strong>Text:</strong> ${ref.textContent}<br>
                        <strong>data-section-id:</strong> ${sectionId || 'missing'}<br>
                        <strong>data-table-id/category-id:</strong> ${tableId || 'missing'}<br>
                        <button class="btn btn-sm btn-outline-primary test-ref-btn mt-1" 
                                data-section-id="${sectionId}" 
                                data-table-id="${tableId}">Test This Reference</button>
                    </li>`;
                }
                
                output += '</ul>';
            }
            
            // Direct reference links
            const directRefs = document.querySelectorAll('.direct-reference');
            output += `<p>Found ${directRefs.length} direct reference links on the page.</p>`;
            
            // Sample direct references
            const directSampleSize = Math.min(3, directRefs.length);
            if (directSampleSize > 0) {
                output += '<h6>Sample Direct References:</h6><ul>';
                
                for (let i = 0; i < directSampleSize; i++) {
                    const ref = directRefs[i];
                    const refId = ref.getAttribute('data-ref-id');
                    
                    output += `<li>
                        <strong>Text:</strong> ${ref.textContent}<br>
                        <strong>data-ref-id:</strong> ${refId || 'missing'}<br>
                        <button class="btn btn-sm btn-outline-primary test-direct-ref-btn mt-1" 
                                data-ref-id="${refId}">Test This Direct Reference</button>
                    </li>`;
                }
                
                output += '</ul>';
            }
            
            // Add section on context detection
            output += '<h5>Context Detection Test</h5>';
            output += '<p>Click this button to test the context detection for a simulated paragraph reference:</p>';
            output += '<button id="test-context-detection" class="btn btn-sm btn-primary mb-3">Test Context Detection</button>';
            output += '<div id="context-detection-result"></div>';
            
            // Update debug content
            debugContent.innerHTML = output;
            
            // Attach event handlers for test buttons
            debugContent.querySelectorAll('.test-ref-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section-id');
                    const tableId = this.getAttribute('data-table-id');
                    
                    // Find closest parent li
                    const li = this.closest('li');
                    li.innerHTML += '<div class="mt-2 p-2 bg-light"><strong>Testing...</strong></div>';
                    
                    // Make the API call to test
                    fetch(`/section-content/${tableId}/${encodeURIComponent(sectionId)}`)
                        .then(response => response.json())
                        .then(data => {
                            const resultDiv = li.querySelector('div');
                            resultDiv.innerHTML = `<strong>Result:</strong><br>
                                Status: ${data.error ? 'Error' : 'Success'}<br>
                                Found rows: ${data.data ? data.data.length : 0}<br>
                                <button class="btn btn-sm btn-outline-secondary mt-1" onclick="console.log('API Response:', ${JSON.stringify(data)})">Log Full Response</button>`;
                        })
                        .catch(error => {
                            const resultDiv = li.querySelector('div');
                            resultDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
                        });
                });
            });
            
            // Test direct reference links
            debugContent.querySelectorAll('.test-direct-ref-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const refId = this.getAttribute('data-ref-id');
                    
                    // Find closest parent li
                    const li = this.closest('li');
                    li.innerHTML += '<div class="mt-2 p-2 bg-light"><strong>Testing...</strong></div>';
                    
                    // Make the API call to test
                    fetch(`/reference/${encodeURIComponent(refId)}`)
                        .then(response => response.json())
                        .then(data => {
                            const resultDiv = li.querySelector('div');
                            resultDiv.innerHTML = `<strong>Result:</strong><br>
                                Status: ${data.error ? 'Error' : 'Success'}<br>
                                <button class="btn btn-sm btn-outline-secondary mt-1" onclick="console.log('API Response:', ${JSON.stringify(data)})">Log Full Response</button>`;
                        })
                        .catch(error => {
                            const resultDiv = li.querySelector('div');
                            resultDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
                        });
                });
            });
            
            // Context detection test
            document.getElementById('test-context-detection').addEventListener('click', function() {
                const resultDiv = document.getElementById('context-detection-result');
                resultDiv.innerHTML = '<div class="p-2 bg-light">Detecting context...</div>';
                
                // Find a section element to test with
                const sections = document.querySelectorAll('.section-section');
                if (sections.length > 0) {
                    // Pick a random section
                    const randomIndex = Math.floor(Math.random() * sections.length);
                    const section = sections[randomIndex];
                    
                    // Try to get the section ID
                    const sectionHeading = section.querySelector('.clickable-heading');
                    let sectionId = sectionHeading ? sectionHeading.getAttribute('data-section-id') : 'unknown';
                    
                    // Find a subsection if it exists
                    const subsection = section.querySelector('.subsection-section');
                    let subsectionId = '';
                    if (subsection) {
                        const subsectionHeading = subsection.querySelector('.clickable-heading');
                        subsectionId = subsectionHeading ? subsectionHeading.getAttribute('data-section-id') : '';
                    }
                    
                    // Create a dummy reference element inside this section
                    const dummyRef = document.createElement('span');
                    dummyRef.className = 'ref';
                    dummyRef.setAttribute('data-section-id', '(a)');
                    dummyRef.setAttribute('data-table-id', '{{ $legalTable->id }}');
                    dummyRef.textContent = 'paragraph (a)';
                    
                    // Temporarily append it to the section
                    section.appendChild(dummyRef);
                    
                    // Now test the context detection
                    let contextSection = '';
                    let contextSubsection = '';
                    
                    // Try to find context
                    const dummySectionContainer = dummyRef.closest('.section-section');
                    if (dummySectionContainer) {
                        const dummySectionHeading = dummySectionContainer.querySelector('.clickable-heading');
                        if (dummySectionHeading) {
                            contextSection = dummySectionHeading.getAttribute('data-section-id');
                        }
                        
                        // Look for subsection
                        const dummySubsectionContainer = dummyRef.closest('.subsection-section');
                        if (dummySubsectionContainer) {
                            const dummySubsectionHeading = dummySubsectionContainer.querySelector('.clickable-heading');
                            if (dummySubsectionHeading) {
                                contextSubsection = dummySubsectionHeading.getAttribute('data-section-id');
                            }
                        }
                    }
                    
                    // Remove the dummy element
                    dummyRef.remove();
                    
                    // Show results
                    resultDiv.innerHTML = `
                        <div class="p-3 bg-light">
                            <h6>Context Detection Results:</h6>
                            <p>
                                <strong>Selected section:</strong> ${sectionId}<br>
                                <strong>Selected subsection:</strong> ${subsectionId || 'none'}<br>
                                <strong>Detected context section:</strong> ${contextSection || 'none'}<br>
                                <strong>Detected context subsection:</strong> ${contextSubsection || 'none'}
                            </p>
                            <div class="alert ${contextSection === sectionId ? 'alert-success' : 'alert-danger'}">
                                Context detection is ${contextSection === sectionId ? 'working correctly' : 'not working correctly'}
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-warning">No section elements found to test with.</div>';
                }
            });
        }, 100);    }
</script>

@push('scripts')
<!-- The push directive needs to be here -->
@endpush

@push('styles')
<style>
    .clickable-heading {
        cursor: pointer;
        color: #007bff;
        transition: color 0.2s;
    }
    .clickable-heading:hover {
        color: #0056b3;
        text-decoration: underline;
    }    .ref {
        color: #28a745;
        cursor: pointer;
        font-weight: 600;
        text-decoration: underline;
        transition: all 0.2s;
        padding: 0 2px;
        border-radius: 3px;
        position: relative;
    }
    .ref:hover {
        color: #218838;
        background-color: rgba(40, 167, 69, 0.1);
    }
    .ref:after {
        content: " 🔍";
        font-size: 10px;
        vertical-align: super;
    }
    .direct-reference {
        color: #3b82f6;
        cursor: pointer;
        margin-left: 5px;
        font-size: 14px;
        vertical-align: middle;
        transition: transform 0.2s;
    }
    .direct-reference:hover {
        transform: scale(1.2);
    }
    .section-btn {
        font-size: 10px;
        line-height: 1;
        margin-left: 2px;
        vertical-align: middle;
    }
    .legal-content {
        padding-left: 1.5rem;
        border-left: 1px solid #dee2e6;
    }
    .legal-section {
        margin-bottom: 1rem;
    }
    .footnote {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    .section-content {
        max-height: 70vh;
        overflow-y: auto;
    }
    .section-item {
        padding: 0.75rem;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
        margin-bottom: 1rem;
    }
    .legal-text-content {
        line-height: 1.6;
    }
    .modal.draggable .modal-dialog {
        cursor: move;
    }
    .modal-header.draggable {
        cursor: move;
        user-select: none;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('user_assets/js/legal-reference-popups.js') }}"></script>
<script src="{{ asset('user_assets/js/reference-by-id.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make all clickable headings act like references
        document.querySelectorAll('.clickable-heading').forEach(function(elem) {
            elem.addEventListener('click', function(e) {
                e.preventDefault();
                const sectionId = this.getAttribute('data-section-id');
                const tableId = this.getAttribute('data-table-id');
                if (sectionId && tableId) {
                    // Create popup with loading state
                    const popup = createFloatingPopup(
                        this.textContent.trim(), 
                        '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading content...</p></div>', 
                        this
                    );
                    
                    // Fetch content
                    fetch(`/section-content/${tableId}/${encodeURIComponent(sectionId)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error === false && data.data && Array.isArray(data.data) && data.data.length > 0) {
                                let html = '';
                                data.data.forEach(function(section) {
                                    html += `<div class="section-item mb-3 p-3 border-bottom">`;
                                    if (section.title) {
                                        html += `<h5 class="section-title text-primary mb-2">${section.title}</h5>`;
                                    }
                                    if (section.text_content) {
                                        html += `<div class="section-text mb-2">${section.text_content}</div>`;
                                    } else if (section.section_text) {
                                        html += `<div class="section-text mb-2">${section.section_text}</div>`;
                                    } 
                                    html += `<div class="section-meta small text-muted">`;
                                    if (section.section_id) {
                                        html += `<div>Section: ${section.section_id}</div>`;
                                    }
                                    html += `</div>`;
                                    html += `</div>`;
                                });
                                popup.querySelector('.popup-content').innerHTML = html;
                                setTimeout(attachReferenceHandlers, 100);
                                setTimeout(initializeReferences, 100); // Initialize direct references too
                            } else {
                                popup.querySelector('.popup-content').innerHTML = '<div class="alert alert-info">No content found for this section.</div>';
                            }
                        })
                        .catch(error => {
                            popup.querySelector('.popup-content').innerHTML = `
                                <div class="alert alert-warning">
                                    <p>Error loading content: ${error.message}</p>
                                    <button class="btn btn-sm btn-primary mt-2 retry-btn">Retry</button>
                                </div>
                            `;
                            popup.querySelector('.retry-btn')?.addEventListener('click', () => {
                                this.click();
                                popup.remove();
                            });
                        });
                }
            });
        });

        // Example of adding direct reference IDs to elements
        document.querySelectorAll('.legal-text').forEach(function(textElem) {
            const rowId = textElem.getAttribute('data-row-id');
            if (rowId) {
                // Add a small reference button after each legal text
                const refButton = document.createElement('button');
                refButton.className = 'btn btn-sm btn-outline-primary ms-2';
                refButton.setAttribute('data-ref-id', '{{ $legalTable->id }}:' + rowId);
                refButton.innerHTML = '<i class="fas fa-link"></i>';
                refButton.title = 'Direct reference to this text';
                textElem.appendChild(refButton);
            }
        });
    });

    // Debug function to check various components
    function runDebugChecks() {
        console.log('==== DEBUG CHECKS ====');
        
        // Check if JavaScript libraries loaded
        console.log('legal-reference-popups.js loaded:', typeof createFloatingPopup === 'function');
        console.log('reference-by-id.js loaded:', typeof initializeReferences === 'function');
        
        // Check clickable references
        const refs = document.querySelectorAll('.ref');
        console.log('References found:', refs.length);
        refs.forEach((ref, i) => {
            if (i < 5) { // Just log first 5 to avoid console spam
                console.log(`Ref ${i+1}:`, {
                    'text': ref.textContent,
                    'section-id': ref.getAttribute('data-section-id'),
                    'table-id': ref.getAttribute('data-table-id')
                });
            }
        });
        
        // Check direct references
        const directRefs = document.querySelectorAll('[data-ref-id]');
        console.log('Direct references found:', directRefs.length);
        directRefs.forEach((ref, i) => {
            if (i < 5) {
                console.log(`Direct ref ${i+1}:`, {
                    'text': ref.textContent,
                    'ref-id': ref.getAttribute('data-ref-id')
                });
            }
        });
        
        // Test the API endpoints
        if (refs.length > 0) {
            const testRef = refs[0];
            const sectionId = testRef.getAttribute('data-section-id');
            const tableId = testRef.getAttribute('data-table-id');
            console.log('Testing API with first reference:', sectionId, tableId);
            
            fetch(`/section-content/${tableId}/${encodeURIComponent(sectionId)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('API response:', data);
                })
                .catch(error => {
                    console.error('API error:', error);
                });
        }
        
        if (directRefs.length > 0) {
            const testDirectRef = directRefs[0];
            const refId = testDirectRef.getAttribute('data-ref-id');
            console.log('Testing direct API with first reference:', refId);
            
            fetch(`/reference/${encodeURIComponent(refId)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Direct API response:', data);
                })
                .catch(error => {
                    console.error('Direct API error:', error);
                });
        }
        
        // Create a test popup
        const testPopup = createFloatingPopup(
            'Test Popup',            'This is a test popup to verify the popup system is working correctly.', 
            document.getElementById('debug-btn')
        );
        console.log('Test popup created:', testPopup);
        
        alert('Debug checks completed! See browser console for details.');
    }
    
    // Test section lookup function
    function testSectionLookup() {
        const input = document.getElementById('test-section-input');
        const resultDiv = document.getElementById('test-section-result');
        
        const sectionValue = input.value.trim();
        if (!sectionValue) {
            resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a section number</div>';
            resultDiv.style.display = 'block';
            return;
        }
        
        const tableId = "{{ $legalTable->id }}";
        resultDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Testing section lookup...</div>';
        resultDiv.style.display = 'block';
        
        // Make the direct API call
        fetch(`/section-content/${tableId}/${encodeURIComponent(sectionValue)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Section lookup test result:', data);
                
                let html = '<div class="p-3 border rounded">';
                html += '<h6>API Response:</h6>';
                
                if (data.error === false && data.data && data.data.length > 0) {
                    html += '<div class="alert alert-success">Found content successfully!</div>';
                    html += '<pre class="bg-light p-2" style="max-height:200px;overflow:auto;">' + 
                            JSON.stringify(data, null, 2) + '</pre>';
                    
                    // Create a test popup with the content
                    const section = data.data[0];
                    const popupContent = section.text_content || section.section_text || 'No content';
                    createFloatingPopup('Section ' + sectionValue, popupContent, input);
                } else {
                    html += '<div class="alert alert-warning">No content found</div>';
                    html += '<pre class="bg-light p-2" style="max-height:200px;overflow:auto;">' + 
                            JSON.stringify(data, null, 2) + '</pre>';
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Section lookup test error:', error);
                resultDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
    }
</script>
@endpush
