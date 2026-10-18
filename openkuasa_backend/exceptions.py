"""Custom exceptions for OpenKuasaOS Backend Services."""


class UnbalancedJournalException(Exception):
    """Raised when general ledger journal debit and credit totals do not balance."""
    pass


class InsufficientLeaveBalanceException(Exception):
    """Raised when an employee requests more leave days than their available quota."""
    pass


class LeaveOverlapException(Exception):
    """Raised when an employee's requested leave dates overlap with an existing leave request."""
    pass


class TenantIsolationException(Exception):
    """Raised when cross-tenant data access or forgery is detected."""
    pass


class SuperadminUnauthorizedException(Exception):
    """Raised when a non-superadmin attempts to access the superadmin console."""
    pass


class AccountSuspendedException(Exception):
    """Raised when a suspended user attempts to authenticate."""
    pass

