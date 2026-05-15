@extends('layouts.buyer')

@section('title', 'About')

@push('head')
    <style>
        .about-page {
            background: #f8f9fc;
            color: #1e293b;
        }

        .about-page .section {
            background: transparent;
        }

        .about-banner {
            height: 40vh;
            min-height: 280px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .about-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" opacity="0.06"><circle cx="100" cy="100" r="60" fill="white"/><circle cx="700" cy="200" r="80" fill="white"/><circle cx="400" cy="500" r="120" fill="white"/><circle cx="200" cy="400" r="40" fill="white"/><circle cx="600" cy="450" r="50" fill="white"/></svg>');
            background-size: cover;
        }

        .about-banner-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
        }

        .about-banner-title {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
        }

        .about-banner-sub {
            font-size: 14px;
            opacity: 0.7;
            font-weight: 400;
        }

        .about-container {
            max-width: 820px;
            margin: 0 auto;
            padding: 48px 24px 64px;
        }

        .about-body h2 {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 32px;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .about-body p {
            text-align: justify;
            font-size: 14px;
            line-height: 1.9;
            color: #475569;
            margin-bottom: 20px;
        }

        .about-body p:last-child {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="about-page">
        <div class="about-banner">
            <div class="about-banner-content">
                <div class="about-banner-title">About Us</div>
                <div class="about-banner-sub">Premium motorcycle parts, built for enthusiasts.</div>
            </div>
        </div>

        <div class="about-container">
            <div class="about-body">
                <h2>Company Profile</h2>

                <p>
                    Established with a vision to redefine the motorcycle spare parts industry, our platform bridges the gap between premium quality components and the passionate riders who demand nothing less than the best. We believe that every motorcycle tells a story, and the parts that keep it running are chapters in that narrative. From the very beginning, our mission has been to provide a curated selection of authentic, high-performance parts that meet the rigorous standards of modern motorcycles. Whether you ride a classic commuter or a high-performance machine, we ensure that every component we list is sourced from trusted manufacturers and undergoes strict quality verification before reaching your hands. Our commitment to excellence is not just a promise; it is the foundation upon which this platform was built.
                </p>

                <p>
                    Navigating the world of motorcycle spare parts can often be overwhelming, with countless options, varying quality standards, and unclear compatibility information. We set out to change that by creating a seamless, intuitive shopping experience that puts clarity and confidence at the forefront. Each product on our platform is accompanied by detailed specifications, compatibility charts, and high-resolution imagery so that you can make informed decisions with ease. Our catalog is organized not just by product type, but also by motorcycle model, ensuring that you find exactly what you need without the guesswork. By combining technology with a deep understanding of motorcycle mechanics, we have built a platform that simplifies the complex and empowers every rider to maintain their machine with pride.
                </p>

                <p>
                    Behind every order is a team of dedicated professionals who share a common passion for motorcycles. From our logistics coordinators who ensure that your parts arrive promptly and in pristine condition, to our customer support specialists who are always ready to assist with technical inquiries, every member of our team is committed to delivering an exceptional experience. We continuously invest in our infrastructure, from warehouse management systems to real-time inventory tracking, so that you receive accurate stock information and timely deliveries. Our relationship with you does not end at checkout; we believe in building long-term partnerships with our customers, supporting you through every stage of your motorcycle ownership journey with reliable after-sales service and genuine care.
                </p>

                <p>
                    Innovation is at the heart of everything we do. We constantly explore new ways to enhance your shopping experience, from intuitive filtering systems that let you narrow down parts by category, brand, or compatibility, to personalized recommendations that help you discover components you might not have considered. Our platform is designed to evolve with the needs of our community, incorporating feedback and data-driven insights to refine every touchpoint. We are also committed to sustainability, working with suppliers who adhere to environmentally responsible manufacturing practices and optimizing our packaging to reduce waste. By choosing us, you are not only investing in quality parts but also supporting a more responsible and forward-thinking approach to the motorcycle industry.
                </p>

                <p>
                    Looking ahead, we envision a future where every rider, regardless of their location or budget, has access to premium motorcycle parts and the knowledge to use them effectively. We are expanding our catalog to include more brands, more variants, and more educational resources such as installation guides, maintenance tips, and video tutorials. Our goal is to become more than just an e-commerce store; we aspire to be a trusted companion in your riding journey, a source of inspiration and expertise that empowers you to get the most out of your motorcycle. Whether you are restoring a classic bike, upgrading your daily rider, or building a custom machine from the ground up, we are here to support you with the best parts and the knowledge you need to bring your vision to life.
                </p>
            </div>
        </div>
    </div>
@endsection
