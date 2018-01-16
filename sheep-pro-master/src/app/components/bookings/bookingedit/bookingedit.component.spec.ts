import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BookingeditComponent } from './bookingedit.component';

describe('BookingeditComponent', () => {
  let component: BookingeditComponent;
  let fixture: ComponentFixture<BookingeditComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BookingeditComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BookingeditComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
