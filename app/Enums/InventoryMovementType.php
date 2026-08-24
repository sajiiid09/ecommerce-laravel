<?php
namespace App\Enums;
enum InventoryMovementType: string { case Initial = 'initial'; case Restock = 'restock'; case Adjustment = 'adjustment'; case Damage = 'damage'; case Sale = 'sale'; case Return = 'return'; case Reservation = 'reservation'; case Release = 'release'; case Correction = 'correction'; }
