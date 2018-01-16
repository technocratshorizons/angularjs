import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CusbookingComponent } from './cusbooking.component';

describe('CusbookingComponent', () => {
  let component: CusbookingComponent;
  let fixture: ComponentFixture<CusbookingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CusbookingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CusbookingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
