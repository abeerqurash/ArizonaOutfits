@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">

                <div class="container">

                    <h1>Admin Dashboard</h1>

                    <br>

                    <div class="dashboard-grid">

                        <div class="dashboard-card">
                            <h3>Products</h3>
                            <p>Manage all products.</p>

                            <a href="{{ route('admin.products.index') }}">
                                Manage Products
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Product Categories</h3>
                            <p>Manage parent and child categories.</p>

                            <a href="{{ route('admin.product-categories.index') }}">
                                Manage Categories
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Product Tags</h3>
                            <p>Manage product tags.</p>

                            <a href="{{ route('admin.product-tags.index') }}">
                                Manage Tags
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Coupons</h3>
                            <p>Create and manage coupons.</p>

                            <a href="{{ route('admin.coupons.index') }}">
                                Manage Coupons
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Orders</h3>
                            <p>View customer orders.</p>

                            <a href="#">
                                Manage Orders
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Customers</h3>
                            <p>Manage registered customers.</p>

                            <a href="#">
                                Manage Customers
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Reviews</h3>
                            <p>Approve and manage reviews.</p>

                            <a href="#">
                                Manage Reviews
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Settings</h3>
                            <p>Website ecommerce settings.</p>

                            <a href="#">
                                Manage Settings
                            </a>
                        </div>

                        <div class="dashboard-card">
                            <h3>Blog Posts</h3>

                            <a href="{{ route('admin.posts.index') }}">Manage Posts</a>
                            <br>
                            <a href="{{ route('admin.categories.index') }}">Manage Categories</a>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>

@endsection