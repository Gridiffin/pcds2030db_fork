<?php
/**
 * Dashboard Initiatives Section Partial
 */
?>

<!-- Initiatives Section Wrapper -->
<section class="initiatives-section mb-4">
    <div class="section-header d-flex align-items-center mb-2">
        <i class="fas fa-lightbulb me-2 text-primary"></i>
        <h2 class="h4 fw-bold mb-0">Initiatives</h2>
    </div>
    <div class="initiatives-description mb-3 text-muted">
        Explore your agency's strategic initiatives and their progress. Click an initiative for details.
    </div>
    
    <!-- Initiative Carousel Card -->
    <div class="bento-card carousel-card" id="programCarouselCard">
        <div class="carousel-inner" id="initiativeCarouselInner">
            <?php
            // Fetch initiatives for the agency
            require_once PROJECT_ROOT_PATH . 'app/lib/agencies/initiatives.php';
            $initiatives = get_agency_initiatives($_SESSION['agency_id']);
            if (!$initiatives || count($initiatives) === 0) {
                echo '<div class="carousel-item active text-center py-4">No initiatives found.</div>';
            } else {
                $i = 0;
                foreach ($initiatives as $initiative) {
                    $active = $i === 0 ? 'active' : '';
                    // Fetch detailed info for each initiative
                    $details = get_agency_initiative_details($initiative['initiative_id'], $_SESSION['agency_id']);
                    $name = $details['initiative_name'] ?? '';
                    $code = $details['initiative_number'] ?? '';
                    $desc = $details['initiative_description'] ?? '';
                    $is_active = $details['is_active'] ?? 0;
                    $start = $details['start_date'] ?? '';
                    $end = $details['end_date'] ?? '';
                    $program_count = $details['agency_program_count'] ?? 0;
                    $last_updated = $details['updated_at'] ?? '';
                    
                    // Calculate health score
                    $health_score = 0;
                    $health_desc = 'No Data';
                    $health_color = '#6c757d';
                    
                    if (isset($details['total_program_count']) && $details['total_program_count'] > 0) {
                        $score = 0;
                        $programs = get_initiative_programs_for_agency($details['initiative_id'], $_SESSION['agency_id']);
                        foreach ($programs as $p) {
                            $status = $p['status'] ?? 'active';
                            $normalized = [
                                'not-started' => 'active',
                                'not_started' => 'active',
                                'on-track' => 'active',
                                'on-track-yearly' => 'active',
                                'target-achieved' => 'completed',
                                'monthly_target_achieved' => 'completed',
                                'severe-delay' => 'delayed',
                                'severe_delay' => 'delayed',
                                'delayed' => 'delayed',
                                'completed' => 'completed',
                                'cancelled' => 'cancelled',
                                'on_hold' => 'on_hold',
                                'active' => 'active',
                            ];
                            $status = $normalized[$status] ?? $status;
                            switch ($status) {
                                case 'completed': $score += 100; break;
                                case 'active': $score += 75; break;
                                case 'on_hold': $score += 50; break;
                                case 'delayed': $score += 25; break;
                                case 'cancelled': $score += 10; break;
                                default: $score += 10; break;
                            }
                        }
                        $health_score = round($score / count($programs));
                        if ($health_score >= 80) {
                            $health_desc = 'Excellent – Programs performing well';
                            $health_color = '#28a745';
                        } elseif ($health_score >= 60) {
                            $health_desc = 'Good – Most programs are active';
                            $health_color = '#28a745';
                        } elseif ($health_score >= 40) {
                            $health_desc = 'Fair – Some programs on hold or delayed';
                            $health_color = '#ffc107';
                        } else {
                            $health_desc = 'Poor – Programs need improvement';
                            $health_color = '#dc3545';
                        }
                    }
                    
                    // Timeline progress
                    $timeline_progress = 0;
                    $elapsed_years = $remaining_years = $total_years = 0;
                    if ($start && $end) {
                        $start_dt = new DateTime($start);
                        $end_dt = new DateTime($end);
                        $now = new DateTime();
                        $total_days = $start_dt->diff($end_dt)->days;
                        $elapsed_days = $start_dt->diff($now)->days;
                        $timeline_progress = $total_days > 0 ? min(100, max(0, round(($elapsed_days / $total_days) * 100))) : 0;
                        $total_years = round($total_days / 365, 1);
                        $elapsed_years = round($elapsed_days / 365, 1);
                        $remaining_years = max(0, round(($total_days - $elapsed_days) / 365, 1));
                    }
                    
                    echo "<div class='carousel-item $active py-2 px-2' style='cursor:pointer;' onclick=\"window.location.href='../initiatives/view_initiative.php?id=" . urlencode($details['initiative_id']) . "'\">"
                        . "<div class='d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-2'>"
                        . "<div class='d-flex align-items-center gap-2'>"
                        . "<i class='fas fa-leaf fa-lg me-2'></i>"
                        . "<span class='h5 fw-bold mb-0'>" . htmlspecialchars($name) . "</span>"
                        . ($code ? "<span class='badge bg-primary ms-2'>#" . htmlspecialchars($code) . "</span>" : "")
                        . ($is_active ? "<span class='badge bg-success ms-2'>Active</span>" : "<span class='badge bg-secondary ms-2'>Inactive</span>")
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2'>"
                        . "<span class='small text-muted'>Programs: $program_count</span>"
                        . "</div>"
                        . "</div>"
                        . "<div class='mb-2 text-muted small' style='min-height:2em;'>" . htmlspecialchars(mb_strimwidth($desc, 0, 120, '...')) . "</div>"
                        . "<div class='d-flex flex-wrap align-items-center gap-3 mb-2'>"
                        . "<span><i class='fas fa-calendar-alt me-1'></i>" . ($start && $end ? date('M j, Y', strtotime($start)) . ' – ' . date('M j, Y', strtotime($end)) . " ($total_years years)" : 'Timeline not specified') . "</span>"
                        . "<span><i class='fas fa-hourglass-half me-1'></i>" . ($start && $end ? "$elapsed_years years elapsed, $remaining_years years remaining" : 'Timeline not available') . "</span>"
                        . "</div>"
                        . "<div class='mb-2'>"
                        . "<div class='progress' style='height: 8px; background: #e9ecef;'>"
                        . "<div class='progress-bar' role='progressbar' style='width: $timeline_progress%; background: #11998e;' aria-valuenow='$timeline_progress' aria-valuemin='0' aria-valuemax='100'></div>"
                        . "</div>"
                        . "<div class='small text-muted mt-1'>$timeline_progress% complete</div>"
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2 mb-2'>"
                        . "<div class='health-score-circle' style='background:conic-gradient($health_color 0deg " . ($health_score * 3.6) . "deg, #e9ecef " . ($health_score * 3.6) . "deg 360deg); color:$health_color;'>"
                        . "<span class='fw-bold'>$health_score</span>"
                        . "</div>"
                        . "<span class='small' style='color:$health_color;'>$health_desc</span>"
                        . "</div>"
                        . "<div class='d-flex align-items-center gap-2 mb-2'>"
                        . "<span class='small text-muted'><i class='fas fa-clock me-1'></i>Last Update: " . ($last_updated ? date('Y-m-d', strtotime($last_updated)) : 'N/A') . "</span>"
                        . "</div>"
                        . "<div class='text-center'><span class='small text-muted' style='font-size:0.92em; opacity:0.7;'>Click for details</span></div>"
                        . "</div>";
                    $i++;
                }
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" id="carouselPrevBtn" aria-label="Previous">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" id="carouselNextBtn" aria-label="Next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
        <div class="carousel-indicators mt-2" id="carouselIndicators">
            <!-- JS will populate indicators -->
        </div>
    </div>
</section>
