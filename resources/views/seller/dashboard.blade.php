
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ env('APP_NAME') }}</title>
        @vite(['resources/css/seller.css', 'resources/js/seller.js'])
        <!-- Boxicons -->
        <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
        <!-- My CSS -->
  
        <title>CC</title>
    </head>
    <body>
    
        <!-- SIDEBAR -->
        <section id="sidebar">
            <a href="#" class="brand">
                <img src="{{ asset('assets/seller-img/campconnect.jpg') }}" alt="company img" id="companyimg">
                <span class="text">CampusConnect</span>
            </a>
            <ul class="side-menu top">
                <li class="active">
                    <a href="{{ route('dashboard') }}">
                        <i class='bx bxs-dashboard'></i>
                        <span class="text">Dashboard</span>
                    </a>
                </li>
                <li >
                    <a href="{{ route('myproduct') }}">
                        <i class='bx bxs-shopping-bag-alt'></i>
                        <span class="text">My Store</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class='bx bxs-doughnut-chart'></i>
                        <span class="text">Analytics</span>
                    </a>
                </li>
                <li >
                <a href="{{ route('wallet') }}">
                    <i class='bx bx-wallet'></i>
                    <span class="text">Wallet</span>
                </a>
                </li>
                <li>
                    <a href="#">
                        <i class='bx bxs-message-dots'></i>
                        <span class="text">Message</span>
                    </a>
                </li>
            </ul>
            <ul class="side-menu">
                <li>
                    <a href="#">
                        <i class='bx bxs-cog'></i>
                        <span class="text">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="logout">
                        <i class='bx bxs-log-out-circle'></i>
                        <span class="text">Logout</span>
                    </a>
                </li>
            </ul>
        </section>
        <!-- SIDEBAR -->
    
        <!-- CONTENT -->
        <section id="content">
            <!-- NAVBAR -->
            <nav>
                <i class='bx bx-menu'></i>
                <a href="#" class="nav-link">Categories</a>
                <form action="#">
                    <div class="form-input">
                        <input type="search" placeholder="Search...">
                        <button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
                    </div>
                </form>
              
                <a href="#" class="notification">
                    <i class='bx bxs-bell'></i>
                    <span class="num">8</span>
                </a>
                <a href="#" class="profile">
                    <img src="{{ asset('assets/seller-img/sample-profile.jpg') }}">
                </a>
            </nav>
            <!-- NAVBAR -->
    
            <!-- MAIN -->
            <main>
                <div class="head-title">
                    <div class="left">
                        <h1>Dashboard</h1>
                        <ul class="breadcrumb">
                            <li>
                                <a href="#">Dashboard</a>
                            </li>
                            <li><i class='bx bx-chevron-right'></i></li>
                            <li>
                                <a class="active" href="#">Home</a>
                            </li>
                        </ul>
                    </div>
                   
                </div>
    
                <ul class="box-info">
                    <li>
                        <i class='bx bxs-calendar-check'></i>
                        <span class="text">
                            <h3>5</h3>
                            <p>Orders</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-group'></i>
                        <span class="text">
                            <h3>3</h3>
                            <p>trades</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">
                            <h3>2543</h3>
                            <p>Total Sales</p>
                        </span>
                    </li>
                </ul>
    
                <div class="table-data">
                    <div class="order">
                        <div class="head">
                            <h3>Recent Orders</h3>
                            <i class='bx bx-search'></i>
                            <i class='bx bx-filter'></i>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date Order</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <img src="img/people.png">
                                        <p>John Doe</p>
                                    </td>
                                    <td>01-10-2021</td>
                                    <td><span class="status completed">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="img/people.png">
                                        <p>John Doe</p>
                                    </td>
                                    <td>01-10-2021</td>
                                    <td><span class="status pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="img/people.png">
                                        <p>John Doe</p>
                                    </td>
                                    <td>01-10-2021</td>
                                    <td><span class="status process">Process</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="img/people.png">
                                        <p>John Doe</p>
                                    </td>
                                    <td>01-10-2021</td>
                                    <td><span class="status pending">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="img/people.png">
                                        <p>John Doe</p>
                                    </td>
                                    <td>01-10-2021</td>
                                    <td><span class="status completed">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="todo">
                       <!-- <div class="head">
                            <h3>Todos</h3>
                            <i class='bx bx-plus'></i>
                            <i class='bx bx-filter'></i>
                        </div>
                        <ul class="todo-list">
                             <li class="completed">
                                <p>Todo List</p>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                            <li class="completed">
                                <p>Todo List</p>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                            <li class="not-completed">
                                <p>Todo List</p>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                            <li class="completed">
                                <p>Todo List</p>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                            <li class="not-completed">
                                <p>Todo List</p>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                            -->
                        </ul>
                    </div>
                </div>
            </main>
            <!-- MAIN -->
        </section>
        <!-- CONTENT -->
    
        <script src="new.js"></script>
    </body>
    </html>
    