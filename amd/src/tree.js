// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Grading Dashboard Tree AMD module.
 *
 * Handles AJAX-based lazy loading and CSS transition toggling of tree hierarchy nodes.
 *
 * @module     block_gradingdashboard/tree
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/templates', 'core/notification', 'core/str'], function(Ajax, Templates, Notification, Str) {
    'use strict';

    var initialized = false;

    return {
        /**
         * Initialize event delegation handlers for block toggle actions.
         */
        init: function() {
            if (initialized) {
                return;
            }
            initialized = true;

            document.addEventListener('click', function(e) {
                var toggle = e.target.closest('.block-gradingdashboard-toggle');
                if (!toggle) {
                    return;
                }

                e.preventDefault();

                var node = toggle.closest('.block-gradingdashboard-tree-node');
                var targetId = toggle.getAttribute('aria-controls');
                var target = document.getElementById(targetId);

                if (!node || !target) {
                    return;
                }

                var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                // Case 1: Assignment node requiring AJAX loading of students list.
                if (node.classList.contains('block-gradingdashboard-assignment-item')) {
                    if (isExpanded) {
                        // Collapse assignment.
                        toggle.setAttribute('aria-expanded', 'false');
                        node.classList.remove('block-gradingdashboard-expanded');
                    } else {
                        // Expand assignment.
                        toggle.setAttribute('aria-expanded', 'true');
                        node.classList.add('block-gradingdashboard-expanded');

                        if (target.dataset.loaded !== 'true') {
                            // Show skeletons from mustache template.
                            Templates.renderForPromise('block_gradingdashboard/student_skeleton', {}).then(function(res) {
                                if (target.dataset.loaded !== 'true') {
                                    target.innerHTML = res.html;
                                }
                            }).catch(Notification.exception);

                            // Extract CMID directly from toggle button dataset.
                            var cmid = toggle.dataset.cmid;

                            Ajax.call([{
                                methodname: 'block_gradingdashboard_get_assignment_students',
                                args: {cmid: parseInt(cmid, 10)}
                            }])[0].then(function(data) {
                                if (!data.students || data.students.length === 0) {
                                    return Str.get_string('allcaughtup', 'block_gradingdashboard').then(function(allcaughtup) {
                                        target.innerHTML = '<div class="text-muted p-3 text-center">' +
                                            allcaughtup + '</div>';
                                        target.dataset.loaded = 'true';
                                    });
                                }

                                var promises = data.students.map(function(student) {
                                    return Templates.renderForPromise('block_gradingdashboard/student', student);
                                });

                                return Promise.all(promises).then(function(results) {
                                    var html = results.map(function(res) {
                                        return res.html;
                                    }).join('');

                                    target.innerHTML = html;
                                    target.dataset.loaded = 'true';

                                    // Execute any template scripts if necessary.
                                    results.forEach(function(res) {
                                        Templates.runTemplateJS(res.js);
                                    });
                                    return;
                                });
                            }).catch(function(error) {
                                Notification.exception(error);
                                target.innerHTML = '';
                                toggle.setAttribute('aria-expanded', 'false');
                                node.classList.remove('block-gradingdashboard-expanded');
                            });
                        }
                    }
                } else {
                    // Case 2: Course or Section wrapper node (all static elements pre-rendered).
                    toggle.setAttribute('aria-expanded', !isExpanded ? 'true' : 'false');
                    node.classList.toggle('block-gradingdashboard-expanded', !isExpanded);
                }
            });
        }
    };
});
