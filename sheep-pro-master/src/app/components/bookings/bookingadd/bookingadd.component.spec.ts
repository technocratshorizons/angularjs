import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BookingaddComponent } from './bookingadd.component';

describe('BookingaddComponent', () => {
  let component: BookingaddComponent;
  let fixture: ComponentFixture<BookingaddComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BookingaddComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BookingaddComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
