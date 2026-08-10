/**
 * Grading Dashboard Tree AMD module.
 *
 * Handles AJAX-based lazy loading and CSS transition toggling of tree hierarchy nodes.
 *
 * @module     block_gradingdashboard/tree
 * @copyright  2026 M. AFZAL RIAZ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/templates', 'core/notification'], function(Ajax, Templates, Notification) {
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
                                    target.innerHTML = '<div class="text-muted p-3 text-center">' +
                                        M.util.get_string('allcaughtup', 'block_gradingdashboard') + '</div>';
                                    target.dataset.loaded = 'true';
                                    return;
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
