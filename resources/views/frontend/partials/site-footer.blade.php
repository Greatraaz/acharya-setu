    <!-- =========================================================================
         FOOTER STRUCTURE
         ========================================================================= -->
    <footer class="vj-footer">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('frontend/images/logo-dark.png') }}" alt="Vedrix" class="vj-footer-logo vj-footer-logo-dark" style="height: 60px; width: auto;">
                        <img src="{{ asset('frontend/images/logo-light.png') }}" alt="Vedrix" class="vj-footer-logo vj-footer-logo-light" style="height: 60px; width: auto;">
                    </div>
                    <p class="vj-footer-tagline">Mentors Shape Possibilities.</p>
                    <p class="vj-footer-blurb">
                        Vedrix connects ambition with experience through structured mentorship, career-readiness tools and measurable development journeys.
                    </p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <!-- Column 2: Discover -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Discover</h4>
                    <ul>
                        <li><a href="{{ url('about') }}">About Vedrix</a></li>
                        <li><a href="{{ url('about') }}#why">Why Vedrix</a></li>
                        <li><a href="{{ url('about') }}#how-it-works">How It Works</a></li>
                        <li><a href="{{ url('about') }}#mission">Our Philosophy</a></li>
                        <li><a href="{{ url('about') }}#trust">Trust & Safety</a></li>
                        <li><a href="{{ url('about') }}#faqs">FAQs</a></li>
                    </ul>
                </div>

                <!-- Column 3: Your Journey -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Your Journey</h4>
                    <ul>
                        <li><a href="{{ route('home') }}#assessments">Assessments</a></li>
                        <li><a href="{{ route('home') }}#paths">Career Paths</a></li>
                        <li><a href="{{ route('home') }}#journeys">Mentorship Journeys</a></li>
                        <li><a href="{{ route('home') }}#studio">Career Readiness</a></li>
                        <li><a href="{{ route('home') }}#studio">Interview Gym</a></li>
                        <li><a href="{{ route('home') }}#studio">Resume Studio</a></li>
                        <li><a href="{{ route('home') }}#studio">LinkedIn Studio</a></li>
                        <li><a href="{{ route('home') }}#studio">Project Vault</a></li>
                    </ul>
                </div>

                <!-- Column 4: Mentors & Organisations -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Mentors</h4>
                    <ul class="mb-4">
                        <li><a href="{{ route('mentors.search') }}">Find a Mentor</a></li>
                        <li><a href="{{ route('mentors.search') }}">Mentor Categories</a></li>
                        <li><a href="{{ route('home') }}#experiences">Office Hours</a></li>
                        <li><a href="{{ route('register') }}?role=mentor">Become a Mentor</a></li>
                        <li><a href="{{ route('home') }}#mentors">Mentor Resources</a></li>
                    </ul>

                    <h4>Organisations</h4>
                    <ul>
                        <li><a href="{{ route('home') }}#organisations">Colleges & Universities</a></li>
                        <li><a href="{{ route('home') }}#organisations">Placement Cells</a></li>
                        <li><a href="{{ route('home') }}#organisations">Employers</a></li>
                        <li><a href="{{ route('home') }}#organisations">Partnerships</a></li>
                    </ul>
                </div>

                <!-- Column 5: Insights & Support -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h4>Insights</h4>
                    <ul class="mb-4">
                        <li><a href="{{ route('insights.career-guides') }}">Career Guides</a></li>
                        <li><a href="{{ route('insights.mentorship-guides') }}">Mentorship Guides</a></li>
                        <li><a href="{{ route('insights.industry-reports') }}">Industry Reports</a></li>
                        <li><a href="{{ route('insights.blogs.index') }}">Articles & Blogs</a></li>
                        <li><a href="{{ route('insights.white-papers.index') }}">Research & Reports</a></li>
                        <li><a href="{{ route('insights.webinars.index') }}">Webinars & Events</a></li>
                        <li><a href="{{ route('insights.podcasts.index') }}">Podcasts & Videos</a></li>
                        <li><a href="{{ route('insights.download-centre.index') }}">Knowledge Centre</a></li>
                    </ul>

                    <h4>Support & Legal</h4>
                    <ul>
                        <li><a href="{{ url('contact') }}">Contact & Help Centre</a></li>
                        <li><a href="{{ url('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ url('terms') }}">Terms of Use</a></li>
                        <li><a href="{{ url('terms') }}">Mentor Terms & Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="vj-footer-bottom">
                <div>
                    © {{ date('Y') }} <strong>VEDRIX</strong>. All rights reserved. Mentors Shape Possibilities.
                </div>
                <div>
                    <span>hello@vedrix.com</span> • <span>New Delhi, India</span>
                </div>
            </div>
        </div>
    </footer>
