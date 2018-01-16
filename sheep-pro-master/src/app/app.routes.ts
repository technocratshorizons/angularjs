import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from './_guards/index';

import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { BookinglistComponent , BookingaddComponent, BookingeditComponent } from './components/bookings/index'
import { ClientaddComponent, ClientlistComponent } from './components/clients/index';
import { AdminlistComponent, AdminaddComponent } from './components/admin/index';
import { ScanneraddComponent, ScannerlistComponent } from './components/scanner/index';
import { CalendarComponent } from './components/calendar/calendar.component';
import { ProfileComponent } from './components/profile/profile.component';
import { NotfoundComponent } from './components/notfound/notfound.component';
import { ForgetComponent } from './components/forget/forget.component';
import { CusbookingComponent } from './components/cusbooking/cusbooking.component';

const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent, canActivate: [AuthGuard] },
  { path: 'booking/list', component: BookinglistComponent, canActivate: [AuthGuard] },
  { path: 'booking/add', component: BookingaddComponent, canActivate: [AuthGuard] },
  { path: 'booking/edit/:id', component: BookingeditComponent, canActivate: [AuthGuard] },
  { path: 'booking/view/:id', component: BookinglistComponent, canActivate: [AuthGuard] },
  { path: 'clients/list', component: ClientlistComponent, canActivate: [AuthGuard] },
  { path: 'clients/add', component: ClientaddComponent, canActivate: [AuthGuard] },
  { path: 'clients/edit/:id', component: ClientaddComponent, canActivate: [AuthGuard] },
  { path: 'admin/list', component: AdminlistComponent, canActivate: [AuthGuard] },
  { path: 'admin/add', component: AdminaddComponent, canActivate: [AuthGuard] },
  { path: 'admin/edit/:id', component: AdminaddComponent, canActivate: [AuthGuard] },
  { path: 'scanner/list', component: ScannerlistComponent, canActivate: [AuthGuard] },
  { path: 'scanner/add', component: ScanneraddComponent, canActivate: [AuthGuard] },
  { path: 'scanner/edit/:id', component: ScanneraddComponent, canActivate: [AuthGuard] },
  { path: 'calendar', component: CalendarComponent, canActivate: [AuthGuard] },
  { path: 'profile', component: ProfileComponent, canActivate: [AuthGuard] },
  { path: 'login', component: LoginComponent },
  { path: 'forget', component: ForgetComponent },
  { path: 'cusbooking', component: CusbookingComponent },
  {path: '404', component: NotfoundComponent},
   {path: '**', redirectTo: '/404'}
];
export const routing = RouterModule.forRoot(routes);