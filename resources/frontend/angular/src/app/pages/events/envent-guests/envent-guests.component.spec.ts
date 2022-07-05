import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EnventGuestsComponent } from './envent-guests.component';

describe('EnventGuestsComponent', () => {
  let component: EnventGuestsComponent;
  let fixture: ComponentFixture<EnventGuestsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ EnventGuestsComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(EnventGuestsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
