//Modules Import 
import { BrowserModule } from '@angular/platform-browser';
import { LOCALE_ID } from '@angular/core';
import { DatePipe } from '@angular/common';
import { CommonModule } from "@angular/common"
import { NgModule } from '@angular/core';
import { FormsModule , ReactiveFormsModule} from '@angular/forms';
import { HttpModule } from '@angular/http';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { BsDropdownModule } from 'ngx-bootstrap/dropdown';
import { ModalModule  } from 'ngx-bootstrap/modal';
import { SimpleNotificationsModule } from 'angular2-notifications';
import { BrowserAnimationsModule }from '@angular/platform-browser/animations';
import { routing } from './app.routes';
import { DataTablesModule } from 'angular-datatables';
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
import { LaddaModule } from 'angular2-ladda';
import { JasperoConfirmationsModule } from '@jaspero/ng2-confirmations';

//Providers Import
import { BsModalService } from 'ngx-bootstrap/modal';
import { customHttpProvider } from './_helpers/index';
import { AuthGuard } from './_guards/index';
import { AlertService, AuthenticationService, NotficationService, UserService, BookingService } from './_services/index';
import { ConfirmationService } from '@jaspero/ng2-confirmations';


//Pipes
import { joiningDays, introducedDays, roomFreeDays, scanDate, idealDays, SafePipe } from './pipes/days.pipe';

//Component Import
import { AlertComponent } from './_directives/index';
import { AppComponent } from './app.component';
import { AppheaderComponent } from './components/appheader/appheader.component';
import { AppsidebarComponent } from './components/appsidebar/appsidebar.component';
import { AppfooterComponent } from './components/appfooter/appfooter.component';
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ClientaddComponent, ClientlistComponent } from './components/clients/index';
import { BookingaddComponent, BookinglistComponent, JoiningComponent, BookingeditComponent } from './components/bookings/index';
import { AdminaddComponent, AdminlistComponent } from './components/admin/index';
import { ScanneraddComponent, ScannerlistComponent } from './components/scanner/index';
import { CalendarComponent } from './components/calendar/calendar.component';
import { ProfileComponent } from './components/profile/profile.component';
import { OnlyNumber } from './_directives/onlynumber.directive';
import { NotfoundComponent } from './components/notfound/notfound.component';
import { ForgetComponent } from './components/forget/forget.component';
import { CusbookingComponent } from './components/cusbooking/cusbooking.component';
import { MomentModule } from 'angular2-moment';

@NgModule({
  declarations: [
    OnlyNumber,
    scanDate,
    idealDays,
    SafePipe,
    joiningDays,
    introducedDays,
    roomFreeDays,
    AppComponent,
    AlertComponent,
    AppheaderComponent,
    AppsidebarComponent,
    AppfooterComponent,
    LoginComponent,
    DashboardComponent,
    BookinglistComponent,
    ClientlistComponent,
    ClientaddComponent,
    BookingaddComponent,
    AdminlistComponent,
    AdminaddComponent,
    ScanneraddComponent,
    ScannerlistComponent,
    CalendarComponent,
    JoiningComponent,
    BookingeditComponent,
    ProfileComponent,
    NotfoundComponent,
    ForgetComponent,
    CusbookingComponent
  ],
  imports: [
  CommonModule,
    BrowserModule,
    routing,
    DataTablesModule,
    FormsModule,
    HttpModule,
    BsDatepickerModule.forRoot(),
    BrowserAnimationsModule,
    SimpleNotificationsModule.forRoot(),
    LaddaModule.forRoot({
            style: "slide-left",
            spinnerSize: 30,
            spinnerColor: "white",
            spinnerLines: 12
        }),
    ModalModule.forRoot(),
    JasperoConfirmationsModule,
    ReactiveFormsModule,
    BsDropdownModule.forRoot(),
    MomentModule
  ],
  providers: [
        { provide: LOCALE_ID, useValue: "en-US" },
        customHttpProvider,
        AuthGuard,
        AlertService,
        AuthenticationService,
        NotficationService,
        BsModalService,
        ConfirmationService,
        UserService,
        BookingService,
        DatePipe
    ],
  bootstrap: [AppComponent]
})
export class AppModule { }
