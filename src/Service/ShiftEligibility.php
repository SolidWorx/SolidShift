<?php

declare(strict_types=1);

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Service;

use App\Entity\Occurrence;
use App\Entity\Shift;
use App\Entity\ShiftRequirement;
use App\Entity\User;
use App\Repository\ShiftRepository;
use DateTimeInterface;

/**
 * Decides why a given user might be a poor fit for a given ShiftRequirement.
 *
 * Returns an empty list when the assignment is unambiguous. All findings are
 * surfaced as soft warnings — the UI decorates the user with a warning dot
 * but does not block the save. The reason for soft-only:
 *   - Role mismatch can be a legitimate one-off cover by an admin.
 *   - UserAreaManagement represents *management* responsibility, not work
 *     eligibility, so it is intentionally not consulted here.
 */
final class ShiftEligibility
{
    public function __construct(
        private readonly ShiftRepository $shiftRepository,
    ) {
    }

    /**
     * @return list<string>
     */
    public function warningsFor(User $user, ShiftRequirement $requirement, Occurrence $occurrence): array
    {
        $warnings = [];

        $site = $occurrence->getSchedule()->getSite();
        $assignedRoles = $user->getRolesForSite($site);

        $hasRole = false;
        foreach ($assignedRoles as $role) {
            if ($role === $requirement->getRole()) {
                $hasRole = true;

                break;
            }
        }

        if (! $hasRole) {
            $warnings[] = sprintf('Not assigned the "%s" role on this site', $requirement->getRole()->getName() ?? 'unknown');
        }

        $reqStart = $requirement->getStartTime();
        $reqEnd = $requirement->getEndTime();
        $reqStartSeconds = self::secondsOfDay($reqStart);
        $reqEndSeconds = self::secondsOfDay($reqEnd);

        foreach ($this->shiftRepository->findByUserAndDate($user, $occurrence->getDate(), $site) as $existing) {
            if ($existing->getOccurrence() === $occurrence && $existing->getRequirement() === $requirement) {
                continue;
            }

            if (self::overlaps($existing, $reqStartSeconds, $reqEndSeconds)) {
                $warnings[] = sprintf(
                    'Already on a shift on %s%s',
                    $occurrence->getDate()->format('Y-m-d'),
                    $existing->getStartTime() instanceof DateTimeInterface ? ' at ' . $existing->getStartTime()->format('H:i') : '',
                );
            }
        }

        return $warnings;
    }

    private static function overlaps(Shift $existing, ?int $reqStart, ?int $reqEnd): bool
    {
        $exStart = self::secondsOfDay($existing->getStartTime());
        $exEnd = self::secondsOfDay($existing->getEndTime());

        if ($reqStart === null || $reqEnd === null || $exStart === null || $exEnd === null) {
            return true;
        }

        return $reqStart < $exEnd && $exStart < $reqEnd;
    }

    private static function secondsOfDay(?DateTimeInterface $time): ?int
    {
        if (! $time instanceof DateTimeInterface) {
            return null;
        }

        return (int) $time->format('H') * 3600 + (int) $time->format('i') * 60 + (int) $time->format('s');
    }
}
